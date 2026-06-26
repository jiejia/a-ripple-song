import { Howl, Howler } from 'howler';
import AudioMotionAnalyzer from 'audiomotion-analyzer';
import { buildRestUrl, bumpPlayCountDom, METRIC_ACTIONS, sendMetric } from '@scripts/lib/rest.js';
import { safeLocalStorage } from '@scripts/lib/storage.js';
import { scheduleIconRefresh } from '@scripts/lib/icons.js';
import { renderSocialIcons } from '@scripts/lib/social-icons.js';
import {
  ANALYZER_OPTIONS,
  AUTOPLAY_COUNTDOWN_SECONDS,
  EPISODE_REST_TYPE,
  PLAYER_STORAGE_KEYS,
  PLAYBACK_RATES,
} from '@scripts/player/constants.js';
import { mergeEpisodesIntoPlaylist, parseEpisodeFromRestPost } from '@scripts/player/episode.js';
import {
  ensureToneContext,
  getPitchCompensationSemitones,
  loadToneModule,
  safeDisconnect,
} from '@scripts/player/audio.js';

const { __ } = wp.i18n;

/**
 * Register the Alpine player store.
 *
 * @param {import('alpinejs').Alpine} Alpine Alpine instance.
 * @return {void}
 */
export function registerPlayerStore(Alpine) {
  Alpine.store('player', {
    currentSound: null,
    soundId: null,
    audioMotion: null,
    analyzerAudioContext: null,
    analyzerSourceNode: null,
    analyzerRebindTimer: null,
    audioContext: null,
    audioSourceNode: null,
    pitchShiftNode: null,
    toneContextReady: false,
    isPlaying: false,
    isLoading: false,
    currentTime: 0,
    duration: 0,
    volume: 0.5,
    isMuted: false,
    lastVolume: 1,
    volumePanelOpen: false,
    timer: null,
    volumeGainNode: null,
    playbackRate: 1,
    availableRates: PLAYBACK_RATES,
    playbackRatePanelOpen: false,
    showAutoplayConfirm: false,
    pendingAutoplay: false,
    autoplayConfirmTimer: null,
    autoplayCountdown: AUTOPLAY_COUNTDOWN_SECONDS,
    autoplayCountdownTimer: null,
    playlist: [],
    currentIndex: 0,
    currentEpisode: null,

    get currentTimeText() {
      return this.formatTime(this.currentTime);
    },

    get durationText() {
      return this.formatTime(this.duration);
    },

    get playbackRateText() {
      return this.playbackRate === 1 ? '1x' : `${this.playbackRate}x`;
    },

    /**
     * Convert volume input into a safe gain value.
     *
     * @param {number|string} volume Volume input from Alpine or storage.
     * @return {number} Normalized volume between 0 and 1.
     */
    normalizeVolume(volume) {
      const parsedVolume = Number.parseFloat(volume);

      if (!Number.isFinite(parsedVolume)) {
        return this.volume;
      }

      return Math.min(Math.max(parsedVolume, 0), 1);
    },

    get currentEpisodePublishDate() {
      if (!this.currentEpisode?.publishDate) {
        return '-';
      }

      return window.formatLocalizedDate(this.currentEpisode.publishDate);
    },

    async init() {
      this.loadPlaylist();
      this.loadVolume();
      this.loadPlaybackRate();

      const playbackState = this.loadPlaybackState();
      if (this.playlist.length === 0) {
        const hydrated = this.hydratePlaylistFromServer(true);
        if (!hydrated) {
          await this.fetchLatestPodcast(true);
        }
        return;
      }

      const episode = this.playlist[this.currentIndex];
      if (!episode) {
        return;
      }

      this.currentEpisode = episode;
      this.loadTrack(episode.audioUrl);

      if (playbackState.currentTime > 0) {
        this.currentTime = playbackState.currentTime;
        this.currentSound.once('load', () => {
          this.seek(playbackState.currentTime);
          if (playbackState.isPlaying) {
            this.showAutoplayConfirmDialog();
          }
        });
      } else if (playbackState.isPlaying) {
        this.showAutoplayConfirmDialog();
      }
    },

    hydratePlaylistFromServer(autoPlay = false) {
      const episodes = window.aripplesongData?.latestPlaylistEpisodes;
      if (!Array.isArray(episodes) || episodes.length === 0) {
        return false;
      }

      const { firstNewEpisode } = mergeEpisodesIntoPlaylist(
        episodes,
        this.playlist,
        (episode) => this.addEpisodeToPlaylist(episode),
      );

      if (autoPlay && firstNewEpisode) {
        this.prepareAutoplayCandidate(firstNewEpisode);
      }

      return this.playlist.length > 0;
    },

    async fetchLatestPodcast(autoPlay = false) {
      try {
        const apiUrl = buildRestUrl(`wp/v2/${EPISODE_REST_TYPE}`, new URLSearchParams({
          per_page: '5',
          orderby: 'date',
          order: 'desc',
          _embed: '',
        }));

        const response = await fetch(apiUrl);
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const posts = await response.json();
        if (!posts.length) {
          return [];
        }

        const episodes = posts
          .map((post) => parseEpisodeFromRestPost(post))
          .filter(Boolean);

        const { addedEpisodes, firstNewEpisode } = mergeEpisodesIntoPlaylist(
          episodes,
          this.playlist,
          (episode) => this.addEpisodeToPlaylist(episode),
        );

        if (autoPlay && firstNewEpisode) {
          this.prepareAutoplayCandidate(firstNewEpisode);
        }

        return addedEpisodes;
      } catch (error) {
        console.error(__('Failed to fetch latest podcasts:', 'a-ripple-song'), error);
        return [];
      }
    },

    prepareAutoplayCandidate(episode) {
      const index = this.playlist.findIndex((item) => item.id === episode.id);
      if (index === -1) {
        return false;
      }

      this.currentIndex = index;
      this.currentEpisode = episode;
      this.loadTrack(episode.audioUrl);
      this.savePlaylist();
      this.showAutoplayConfirmDialog();
      return true;
    },

    showAutoplayConfirmDialog() {
      this.pendingAutoplay = true;
      this.showAutoplayConfirm = true;
      this.autoplayCountdown = AUTOPLAY_COUNTDOWN_SECONDS;
      scheduleIconRefresh();
      renderSocialIcons();

      this.autoplayCountdownTimer = window.setInterval(() => {
        this.autoplayCountdown -= 1;
        if (this.autoplayCountdown <= 0) {
          this.cancelAutoplay();
        }
      }, 1000);
    },

    clearAutoplayTimers() {
      if (this.autoplayConfirmTimer) {
        window.clearTimeout(this.autoplayConfirmTimer);
        this.autoplayConfirmTimer = null;
      }

      if (this.autoplayCountdownTimer) {
        window.clearInterval(this.autoplayCountdownTimer);
        this.autoplayCountdownTimer = null;
      }
    },

    confirmAutoplay() {
      this.clearAutoplayTimers();
      this.showAutoplayConfirm = false;
      this.pendingAutoplay = false;
      this.play();
    },

    cancelAutoplay() {
      this.clearAutoplayTimers();
      this.showAutoplayConfirm = false;
      this.pendingAutoplay = false;
      this.isPlaying = false;
      this.savePlaybackState();
    },

    resetAudioChain({ unloadSound = false } = {}) {
      if (unloadSound && this.currentSound) {
        this.currentSound.stop();
        this.currentSound.unload();
      }

      this.soundId = null;
      this.destroyAnalyzer();
      safeDisconnect(this.volumeGainNode);
      this.volumeGainNode = null;

      if (this.pitchShiftNode) {
        this.pitchShiftNode.dispose();
        this.pitchShiftNode = null;
      }

      this.audioContext = null;
      this.audioSourceNode = null;
      this.toneContextReady = false;
    },

    loadTrack(audioUrl) {
      this.resetAudioChain({ unloadSound: true });
      this.isLoading = true;

      this.currentSound = new Howl({
        src: [audioUrl],
        volume: 1,
        onplay: async () => {
          this.isPlaying = true;
          await this.ensureAudioGraph();
        },
        onseek: () => {
          this.currentTime = Number(this.currentSound?.seek(this.soundId)) || 0;
          this.scheduleAnalyzerRebind();
        },
        onpause: () => {
          this.isPlaying = false;
        },
        onload: () => {
          this.duration = this.currentSound.duration();
          this.isLoading = false;
        },
        onloaderror: (_id, error) => {
          this.isLoading = false;
          console.error(__('Audio load error:', 'a-ripple-song'), error);
        },
        onend: () => {
          this.playNext();
        },
      });
    },

    async play() {
      if (!this.currentSound) {
        return;
      }

      this.toneContextReady = await ensureToneContext(this.toneContextReady);

      if (this.showAutoplayConfirm) {
        this.clearAutoplayTimers();
        this.showAutoplayConfirm = false;
        this.pendingAutoplay = false;
      }

      if (this.soundId === null) {
        if (this.currentEpisode?.id) {
          bumpPlayCountDom(this.currentEpisode.id);
          void sendMetric(METRIC_ACTIONS.play, this.currentEpisode.id);
        }

        this.soundId = this.currentSound.play();
        this.currentSound.rate(this.playbackRate, this.soundId);
      } else {
        this.currentSound.play(this.soundId);
      }

      this.isPlaying = true;
      this.startProgressTimer();
      this.savePlaybackState();
    },

    pause() {
      if (!this.currentSound) {
        return;
      }

      this.currentSound.pause(this.soundId);
      this.isPlaying = false;
      this.stopProgressTimer();
      this.savePlaybackState();
    },

    togglePlay() {
      if (this.isPlaying) {
        this.pause();
      } else {
        this.play();
      }
    },

    seek(position) {
      if (!this.currentSound) {
        return;
      }

      const nextPosition = Number.parseFloat(position);
      this.currentSound.seek(nextPosition);
      this.currentTime = nextPosition;
      this.savePlaybackState();
    },

    setVolume(volume) {
      const normalizedVolume = this.normalizeVolume(volume);

      this.volume = normalizedVolume;

      if (this.volumeGainNode) {
        this.volumeGainNode.gain.value = normalizedVolume;
      }

      this.isMuted = normalizedVolume === 0;
      if (normalizedVolume > 0) {
        this.lastVolume = normalizedVolume;
      }

      scheduleIconRefresh();
      renderSocialIcons();
      this.saveVolume();
    },

    toggleVolumePanel() {
      this.volumePanelOpen = !this.volumePanelOpen;
    },

    toggleMute() {
      if (this.isMuted) {
        this.setVolume(this.lastVolume);
      } else {
        this.lastVolume = this.volume;
        this.setVolume(0);
      }

      scheduleIconRefresh();
      renderSocialIcons();
    },

    cyclePlaybackRate() {
      const currentIndex = this.availableRates.indexOf(this.playbackRate);
      const nextIndex = (currentIndex + 1) % this.availableRates.length;
      this.setPlaybackRate(this.availableRates[nextIndex]);
    },

    togglePlaybackRatePanel() {
      this.playbackRatePanelOpen = !this.playbackRatePanelOpen;
    },

    setPlaybackRate(rate) {
      this.playbackRate = rate;

      if (this.currentSound && this.soundId !== null) {
        this.currentSound.rate(rate, this.soundId);
      }

      this.applyPitchCompensation();
      this.playbackRatePanelOpen = false;
      this.savePlaybackRate();
    },

    applyPitchCompensation() {
      if (!this.pitchShiftNode) {
        return;
      }

      const shouldPitchCompensate = this.playbackRate !== 1;
      this.pitchShiftNode.wet.value = shouldPitchCompensate ? 1 : 0;
      this.pitchShiftNode.pitch = shouldPitchCompensate
        ? getPitchCompensationSemitones(this.playbackRate)
        : 0;
    },

    async setupAudioGraph() {
      if (!this.audioContext || !this.audioSourceNode || !this.volumeGainNode) {
        return;
      }

      const canReconnect = typeof this.audioSourceNode.disconnect === 'function'
        && typeof this.audioSourceNode.connect === 'function';

      if (!canReconnect) {
        return;
      }

      const { audioContext, audioSourceNode: sourceNode, volumeGainNode } = this;

      try {
        const Tone = await loadToneModule();

        if (!this.toneContextReady) {
          Tone.setContext(audioContext);
          this.toneContextReady = true;
        }

        if (!this.pitchShiftNode) {
          this.pitchShiftNode = new Tone.PitchShift({ pitch: 0, wet: 0 });
        }

        safeDisconnect(this.pitchShiftNode);
        safeDisconnect(volumeGainNode);
        safeDisconnect(sourceNode);

        Tone.connect(sourceNode, this.pitchShiftNode);
        this.pitchShiftNode.connect(volumeGainNode);
        volumeGainNode.gain.value = this.volume;
        volumeGainNode.connect(audioContext.destination);
        this.applyPitchCompensation();
      } catch (error) {
        console.warn('[aripplesong] Failed to setup Tone.js audio graph; falling back to direct connection', error);
        safeDisconnect(sourceNode);
        safeDisconnect(volumeGainNode);
        sourceNode.connect(volumeGainNode);
        volumeGainNode.gain.value = this.volume;
        volumeGainNode.connect(audioContext.destination);
      }

      this.bindAnalyzer();
    },

    getActiveSoundEntry() {
      return this.currentSound?._sounds?.find((item) => item?._id === this.soundId)
        || this.currentSound?._sounds?.[0]
        || null;
    },

    getActiveSourceNode() {
      const sound = this.getActiveSoundEntry();
      return sound?._node || sound?._node?.bufferSource || null;
    },

    async ensureAudioGraph() {
      if (!this.currentSound) {
        return;
      }

      const sourceNode = this.getActiveSourceNode();
      const audioContext = sourceNode?.context || Howler.ctx;
      if (!sourceNode || !audioContext) {
        return;
      }

      if (this.volumeGainNode && this.audioSourceNode === sourceNode) {
        return;
      }

      if (!this.volumeGainNode) {
        this.volumeGainNode = audioContext.createGain();
        this.volumeGainNode.gain.value = this.volume;
      }

      this.audioContext = audioContext;
      this.audioSourceNode = sourceNode;
      await this.setupAudioGraph();
    },

    bindAnalyzer() {
      if (!this.currentSound) {
        return;
      }

      const container = document.getElementById('wave');
      const sourceNode = this.getActiveSourceNode();
      const sourceContext = sourceNode?.context || null;

      if (!container || !sourceNode || !sourceContext || typeof sourceNode.connect !== 'function') {
        return;
      }

      const shouldRecreateAnalyzer = !this.audioMotion
        || this.audioMotion.isDestroyed
        || this.audioMotion.canvas?.parentElement !== container
        || this.analyzerAudioContext !== sourceContext;

      if (!shouldRecreateAnalyzer && this.analyzerSourceNode === sourceNode) {
        return;
      }

      if (shouldRecreateAnalyzer) {
        this.destroyAnalyzer();
        container.querySelectorAll('canvas').forEach((canvas) => canvas.remove());

        try {
          this.audioMotion = new AudioMotionAnalyzer(container, {
            audioCtx: sourceContext,
            ...ANALYZER_OPTIONS,
          });
          this.analyzerAudioContext = sourceContext;
        } catch {
          this.audioMotion = null;
          this.analyzerAudioContext = null;
          this.analyzerSourceNode = null;
          return;
        }
      } else {
        try {
          this.audioMotion.disconnectInput();
        } catch {
          // Ignore disconnect failures and try reconnecting anyway.
        }
      }

      try {
        this.audioMotion.connectInput(sourceNode);
        this.analyzerSourceNode = sourceNode;
      } catch {
        this.destroyAnalyzer();
        container.querySelectorAll('canvas').forEach((canvas) => canvas.remove());
      }
    },

    scheduleAnalyzerRebind() {
      if (this.analyzerRebindTimer) {
        window.clearTimeout(this.analyzerRebindTimer);
        this.analyzerRebindTimer = null;
      }

      if (!this.currentSound || !this.isPlaying) {
        return;
      }

      this.analyzerRebindTimer = window.setTimeout(() => {
        this.analyzerRebindTimer = null;
        void this.ensureAudioGraph();
      }, 0);
    },

    destroyAnalyzer() {
      if (this.analyzerRebindTimer) {
        window.clearTimeout(this.analyzerRebindTimer);
        this.analyzerRebindTimer = null;
      }

      this.analyzerAudioContext = null;
      this.analyzerSourceNode = null;

      if (!this.audioMotion) {
        return;
      }

      try {
        this.audioMotion.disconnectInput();
      } catch {
        // Ignore disconnect failures while tearing down the analyzer.
      }

      try {
        this.audioMotion.destroy();
      } catch {
        // Ignore destroy failures and continue clearing stale references.
      }

      this.audioMotion = null;
    },

    startProgressTimer() {
      let saveCounter = 0;
      this.timer = window.setInterval(() => {
        if (!this.currentSound || !this.isPlaying) {
          return;
        }

        this.currentTime = this.currentSound.seek(this.soundId) || 0;
        saveCounter += 1;

        if (saveCounter >= 10) {
          this.savePlaybackState();
          saveCounter = 0;
        }
      }, 100);
    },

    stopProgressTimer() {
      if (this.timer) {
        window.clearInterval(this.timer);
        this.timer = null;
      }
    },

    loadPlaylist() {
      const data = safeLocalStorage.getItem(PLAYER_STORAGE_KEYS.playlist);
      this.playlist = data ? JSON.parse(data) : [];

      const index = safeLocalStorage.getItem(PLAYER_STORAGE_KEYS.currentIndex);
      this.currentIndex = index ? Number.parseInt(index, 10) : 0;
    },

    savePlaylist() {
      safeLocalStorage.setItem(PLAYER_STORAGE_KEYS.playlist, JSON.stringify(this.playlist));
      safeLocalStorage.setItem(PLAYER_STORAGE_KEYS.currentIndex, String(this.currentIndex));
      window.dispatchEvent(new CustomEvent('playlist-updated'));
      scheduleIconRefresh();
      renderSocialIcons();
    },

    loadVolume() {
      const savedVolume = safeLocalStorage.getItem(PLAYER_STORAGE_KEYS.volume);
      if (savedVolume === null) {
        return;
      }

      const volume = this.normalizeVolume(savedVolume);
      this.volume = volume;
      this.lastVolume = volume > 0 ? volume : this.lastVolume;
      this.isMuted = volume === 0;
    },

    saveVolume() {
      safeLocalStorage.setItem(PLAYER_STORAGE_KEYS.volume, String(this.volume));
    },

    savePlaybackRate() {
      safeLocalStorage.setItem(PLAYER_STORAGE_KEYS.playbackRate, String(this.playbackRate));
    },

    loadPlaybackRate() {
      const savedRate = safeLocalStorage.getItem(PLAYER_STORAGE_KEYS.playbackRate);
      if (savedRate === null) {
        return;
      }

      const rate = Number.parseFloat(savedRate);
      if (this.availableRates.includes(rate)) {
        this.playbackRate = rate;
      }
    },

    savePlaybackState() {
      safeLocalStorage.setItem(PLAYER_STORAGE_KEYS.currentTime, String(this.currentTime));
      safeLocalStorage.setItem(PLAYER_STORAGE_KEYS.isPlaying, String(this.isPlaying));
    },

    loadPlaybackState() {
      const savedTime = safeLocalStorage.getItem(PLAYER_STORAGE_KEYS.currentTime);
      const savedIsPlaying = safeLocalStorage.getItem(PLAYER_STORAGE_KEYS.isPlaying);

      return {
        currentTime: savedTime ? Number.parseFloat(savedTime) : 0,
        isPlaying: savedIsPlaying === 'true',
      };
    },

    clearPlaybackState() {
      safeLocalStorage.removeItem(PLAYER_STORAGE_KEYS.currentTime);
      safeLocalStorage.removeItem(PLAYER_STORAGE_KEYS.isPlaying);
    },

    startEpisode(episode, { autoplay = true, resetProgress = true } = {}) {
      this.currentEpisode = episode;
      this.loadTrack(episode.audioUrl);

      if (autoplay) {
        this.play();
      }

      this.savePlaylist();

      if (resetProgress) {
        this.currentTime = 0;
        this.savePlaybackState();
      }
    },

    addEpisode(episode) {
      const existingIndex = this.playlist.findIndex((item) => item.id === episode.id);
      if (existingIndex !== -1) {
        this.currentIndex = existingIndex;
        this.startEpisode(episode);
        return;
      }

      this.playlist.push(episode);
      this.currentIndex = this.playlist.length - 1;
      this.startEpisode(episode);
    },

    addEpisodeToPlaylist(episode) {
      if (this.playlist.some((item) => item.id === episode.id)) {
        return false;
      }

      this.playlist.push(episode);
      this.savePlaylist();
      return true;
    },

    removeEpisode(episodeId) {
      this.playlist = this.playlist.filter((item) => item.id !== episodeId);
      if (this.currentIndex >= this.playlist.length) {
        this.currentIndex = Math.max(0, this.playlist.length - 1);
      }

      if (this.playlist.length === 0) {
        this.stopAndClear();
      }

      this.savePlaylist();
    },

    clearPlaylist() {
      this.stopAndClear();
      this.playlist = [];
      this.currentIndex = 0;
      this.currentEpisode = null;
      this.savePlaylist();
    },

    stopAndClear() {
      this.resetAudioChain({ unloadSound: true });
      this.currentSound = null;
      this.stopProgressTimer();
      this.isPlaying = false;
      this.currentTime = 0;
      this.duration = 0;
      this.currentEpisode = null;
      this.clearPlaybackState();
    },

    playEpisodeAtOffset(offset) {
      if (this.playlist.length === 0) {
        return;
      }

      this.currentIndex = (this.currentIndex + offset + this.playlist.length) % this.playlist.length;
      this.startEpisode(this.playlist[this.currentIndex]);
    },

    playNext() {
      this.playEpisodeAtOffset(1);
    },

    playPrevious() {
      this.playEpisodeAtOffset(-1);
    },

    playByIndex(index) {
      if (index < 0 || index >= this.playlist.length) {
        return;
      }

      this.currentIndex = index;
      this.startEpisode(this.playlist[index]);
    },

    formatTime(seconds) {
      if (!seconds || Number.isNaN(seconds)) {
        return '00:00';
      }

      const mins = Math.floor(seconds / 60);
      const secs = Math.floor(seconds % 60);
      return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    },
  });
}
