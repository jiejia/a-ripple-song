import.meta.glob([
  '../images/**',
  '../fonts/**',
]);
import Swup from 'swup';
import { createIcons, icons } from 'lucide';
import { Howl, Howler } from 'howler';
import AudioMotionAnalyzer from 'audiomotion-analyzer';
import SwupFormsPlugin from '@swup/forms-plugin';
import Alpine from 'alpinejs'


window.Alpine = Alpine

// 创建主题 Store
Alpine.store('theme', {
  mode: 'auto', // 'light', 'dark', 'auto'
  storageKey: 'aripplesong-theme-mode',
  lightTheme: 'retro',
  darkTheme: 'dim',
  mediaQuery: null,
  
  init() {
    // 从 localStorage 加载主题模式
    const savedMode = localStorage.getItem(this.storageKey);
    if (savedMode && ['light', 'dark', 'auto'].includes(savedMode)) {
      this.mode = savedMode;
    }
    
    // 监听系统主题变化
    this.mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    this.mediaQuery.addEventListener('change', () => {
      if (this.mode === 'auto') {
        this.applyTheme();
      }
    });
    
    // 应用初始主题
    this.applyTheme();
  },
  
  toggle() {
    // 循环切换：light -> dark -> auto -> light
    if (this.mode === 'light') {
      this.mode = 'dark';
    } else if (this.mode === 'dark') {
      this.mode = 'auto';
    } else {
      this.mode = 'light';
    }
    
    localStorage.setItem(this.storageKey, this.mode);
    this.applyTheme();
  },
  
  setMode(mode) {
    // 直接设置主题模式
    if (['light', 'dark', 'auto'].includes(mode)) {
      this.mode = mode;
      localStorage.setItem(this.storageKey, this.mode);
      this.applyTheme();
    }
  },
  
  applyTheme() {
    let theme;
    if (this.mode === 'auto') {
      // 跟随系统
      theme = this.mediaQuery.matches ? this.darkTheme : this.lightTheme;
    } else if (this.mode === 'dark') {
      theme = this.darkTheme;
    } else {
      theme = this.lightTheme;
    }
    
    document.documentElement.setAttribute('data-theme', theme);
  },
  
  get isDark() {
    if (this.mode === 'auto') {
      return this.mediaQuery ? this.mediaQuery.matches : false;
    }
    return this.mode === 'dark';
  },
  
  get isLight() {
    return this.mode === 'light';
  },
  
  get isAuto() {
    return this.mode === 'auto';
  }
});

// 创建 Alpine Store
Alpine.store('player', {
  // player
  currentSound: null,
  soundId: null,
  audioMotion: null,
  isPlaying: false,
  currentTime: 0,
  duration: 0,
  volume: 0.5,
  isMuted: false,
  lastVolume: 1,
  volumePanelOpen: false,
  timer: null,
  volumeGainNode: null, // 用于独立控制输出音量的 GainNode
  
  // playback rate
  playbackRate: 1.0,
  availableRates: [0.5, 0.75, 1, 1.25, 1.5, 2],
  playbackRatePanelOpen: false,

  // playlist
  playlist: [],
  currentIndex: 0,
  currentEpisode: null,
  storageKey: 'aripplesong-playlist',
  currentIndexKey: 'aripplesong-current-index',
  volumeKey: 'aripplesong-volume',
  currentTimeKey: 'aripplesong-current-time',
  isPlayingKey: 'aripplesong-is-playing',
  playbackRateKey: 'aripplesong-playback-rate',

  // ========== 计算属性 ==========
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
   * 从 WordPress REST API 获取最新5条播客并添加到播放列表
   * @param {boolean} autoPlay - 是否自动播放第一条（默认不播放）
   */
  async fetchLatestPodcast(autoPlay = false) {
    try {
      // 调用 WordPress REST API 获取最新的5条播客
      const response = await fetch('/wp-json/wp/v2/podcast?per_page=5&orderby=date&order=desc&_embed');

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const podcasts = await response.json();

      if (podcasts.length === 0) {
        console.log('❌ 没有找到播客');
        return [];
      }

      console.log(`📥 获取到 ${podcasts.length} 条播客`);

      const addedEpisodes = [];
      let firstNewEpisode = null;

      // 遍历所有播客
      for (const post of podcasts) {
        // 获取音频文件 URL（从自定义字段）
        let audioUrl = post.audio_file || '';

        // 如果没有通过 meta 获取到，尝试从 ACF 或其他方式获取
        if (!audioUrl && post.acf?.audio_file) {
          audioUrl = post.acf.audio_file;
        }

        if (!audioUrl) {
          console.warn('⚠️ 播客没有音频文件，跳过:', post.title.rendered);
          continue;
        }

        // 获取特色图片
        let featuredImage = 'https://cdn.pixabay.com/photo/2025/10/03/09/14/asters-9870320_960_720.jpg';
        if (post._embedded?.['wp:featuredmedia']?.[0]?.source_url) {
          featuredImage = post._embedded['wp:featuredmedia'][0].source_url;
        }

        // 构建 episode 对象
        const episode = {
          id: post.id,
          audioUrl: audioUrl,
          title: post.title.rendered,
          description: post.excerpt.rendered.replace(/<[^>]*>/g, ''), // 移除 HTML 标签
          publishDate: new Date(post.date).toLocaleDateString('zh-CN'),
          featuredImage: featuredImage,
          link: post.link
        };

        // 检查是否已存在
        const existingIndex = this.playlist.findIndex(item => item.id === episode.id);

        if (existingIndex === -1) {
          // 不存在，添加到播放列表（不自动播放）
          this.addEpisodeToPlaylist(episode);
          addedEpisodes.push(episode);

          // 记录第一个新添加的节目
          if (!firstNewEpisode) {
            firstNewEpisode = episode;
          }

          console.log('✅ 已添加:', episode.title);
        } else {
          console.log('⏭️ 已存在，跳过:', episode.title);
        }
      }

      if (addedEpisodes.length > 0) {
        console.log(`✅ 成功添加 ${addedEpisodes.length} 条新播客到播放列表`);

        // 如果需要自动播放且有新节目
        if (autoPlay && firstNewEpisode) {
          // 切换到第一个新添加的节目并播放
          const index = this.playlist.findIndex(item => item.id === firstNewEpisode.id);
          if (index !== -1) {
            this.playByIndex(index);
          }
        }
      } else {
        console.log('ℹ️ 没有新的播客需要添加');
      }

      return addedEpisodes;

    } catch (error) {
      console.error('❌ 获取最新播客失败:', error);
      return [];
    }
  },

  // ========== 初始化 ==========
  async init() {
    // 从本地存储加载播放列表
    this.loadPlaylist();

    // 从本地存储加载音量设置
    this.loadVolume();

    // 从本地存储加载播放速度
    this.loadPlaybackRate();

    // 从本地存储加载播放状态
    const playbackState = this.loadPlaybackState();

    // 如果播放列表为空，则获取最新播客
    if (this.playlist.length == 0) {
      await this.fetchLatestPodcast(true);
      return; // 如果是新加载的播客，fetchLatestPodcast 会自动播放
    }

    // 加载当前播放的节目
    const episode = this.playlist[this.currentIndex];

    console.log('current_episode', episode);

    // 检查 episode 是否存在
    if (!episode) {
      console.log('⚠️ 没有可播放的节目');
      return;
    }

    this.currentEpisode = episode;
    this.loadTrack(episode.audioUrl);

    // 恢复播放进度
    if (playbackState.currentTime > 0) {
      // ⭐ 立即更新 UI 中的进度显示（即使音频还在加载）
      this.currentTime = playbackState.currentTime;
      
      // 等待音频加载完成后再跳转到保存的位置
      this.currentSound.once('load', () => {
        this.seek(playbackState.currentTime);
        console.log('✅ 已恢复播放进度:', playbackState.currentTime);
        
        // 根据保存的状态决定是否自动播放
        if (playbackState.isPlaying) {
          this.play();
          console.log('✅ 已恢复播放状态');
        }
      });
    } else if (playbackState.isPlaying) {
      // 如果没有保存的进度但保存了播放状态，直接播放
      this.play();
      console.log('✅ 已恢复播放状态');
    }
  },

  // ========== 播放器核心方法 ==========
  loadTrack(audioUrl) {
    // 停止当前播放
    if (this.currentSound) {
      this.currentSound.stop();
      this.currentSound.unload();
    }

    // ⭐ 重置 soundId（新增这一行）
    this.soundId = null;

    // 清理 AudioMotion
    if (this.audioMotion) {
      this.audioMotion.destroy();
      this.audioMotion = null;
    }

    // 清理 volumeGainNode
    if (this.volumeGainNode) {
      this.volumeGainNode.disconnect();
      this.volumeGainNode = null;
    }

    // 创建新的 Howl 实例，volume 保持为 1，让波形图获取完整信号
    this.currentSound = new Howl({
      src: [audioUrl],
      volume: 1, // 保持最大音量，音量控制将在 GainNode 中进行
      onplay: () => {
        this.isPlaying = true;
        this.initAudioMotion();
      },
      onpause: () => {
        this.isPlaying = false;
      },
      onload: () => {
        this.duration = this.currentSound.duration();
        // console.log('duration', this.durationText);
      },
      onend: () => {
        this.playNext();
      }
    });
  },

  play() {
    if (!this.currentSound) {
      return;
    }
    if (this.soundId === null) {
      this.soundId = this.currentSound.play();
      // 应用播放速度
      this.currentSound.rate(this.playbackRate, this.soundId);
    } else {
      this.currentSound.play(this.soundId);
    }
    this.isPlaying = true;

    this.startProgressTimer();
    
    // 保存播放状态
    this.savePlaybackState();
  },

  pause() {
    if (!this.currentSound) return;
    this.currentSound.pause(this.soundId);
    this.isPlaying = false;
    this.stopProgressTimer();
    
    // 保存播放状态
    this.savePlaybackState();
  },

  recreateIcons() {
    setTimeout(() => {
      createIcons({ icons });
    }, 10);
  },

  togglePlay() {
    if (this.isPlaying) {
      this.pause();
    } else {
      this.play();
    }
  },

  seek(position) {
    if (!this.currentSound) return;
    this.currentSound.seek(parseFloat(position));
    this.currentTime = parseFloat(position);
    
    // 保存播放进度
    this.savePlaybackState();
  },

  setVolume(volume) {
    this.volume = volume;

    // 使用独立的 GainNode 控制音量，不影响波形图
    if (this.volumeGainNode) {
      this.volumeGainNode.gain.value = volume;
    }

    this.isMuted = volume == 0;
    this.recreateIcons();

    if (volume > 0) {
      this.lastVolume = volume;
    }

    // 保存音量到 localStorage
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

    console.log('isMuted', this.isMuted);

    this.recreateIcons();

  },

  /**
   * 循环切换播放速度
   */
  cyclePlaybackRate() {
    const currentIndex = this.availableRates.indexOf(this.playbackRate);
    const nextIndex = (currentIndex + 1) % this.availableRates.length;
    this.setPlaybackRate(this.availableRates[nextIndex]);
  },

  /**
   * 切换播放速度面板显示状态
   */
  togglePlaybackRatePanel() {
    this.playbackRatePanelOpen = !this.playbackRatePanelOpen;
  },

  /**
   * 设置播放速度
   */
  setPlaybackRate(rate) {
    this.playbackRate = rate;
    if (this.currentSound && this.soundId !== null) {
      this.currentSound.rate(rate, this.soundId);
    }
    // 设置后关闭面板
    this.playbackRatePanelOpen = false;
    
    // 保存播放速度到 localStorage
    this.savePlaybackRate();
  },
  initAudioMotion() {
    if (!this.audioMotion && this.currentSound) {
      const container = document.getElementById('wave');
      if (container) {
        const audioContext = Howler.ctx;
        const sourceNode = this.currentSound._sounds[0]._node;

        // 创建独立的 GainNode 用于音量控制
        this.volumeGainNode = audioContext.createGain();
        this.volumeGainNode.gain.value = this.volume;

        // 断开原有连接
        sourceNode.disconnect();

        // 创建新的音频路径：source -> volumeGainNode -> destination
        sourceNode.connect(this.volumeGainNode);
        this.volumeGainNode.connect(audioContext.destination);

        // AudioMotion 分析原始的 sourceNode（音量控制之前）
        this.audioMotion = new AudioMotionAnalyzer(container, {
          source: sourceNode,
          connectSpeakers: false, // 改为 false，因为我们手动管理连接
          mode: 4,
          alphaBars: false,
          ansiBands: false,
          barSpace: .25,
          channelLayout: 'single',
          colorMode: 'bar-level',
          frequencyScale: 'log',
          gradient: 'prism',
          ledBars: false,
          linearAmplitude: true,
          linearBoost: 1.6,
          lumiBars: false,
          maxFreq: 16000,
          minFreq: 30,
          mirror: 0,
          radial: false,
          reflexRatio: .5,
          reflexAlpha: 1,
          roundBars: true,
          showPeaks: false,
          showScaleX: false,
          smoothing: .7,
          weightingFilter: 'D',
          overlay: true,
          showBgColor: false,
          maxDecibels: -30
        });
      }
    }
  },
  startProgressTimer() {
    let saveCounter = 0;
    this.timer = setInterval(() => {
      if (this.currentSound && this.isPlaying) {
        this.currentTime = this.currentSound.seek(this.soundId) || 0;
        
        // 每10次（约1秒）保存一次播放状态，避免频繁写入
        saveCounter++;
        if (saveCounter >= 10) {
          this.savePlaybackState();
          saveCounter = 0;
        }
      }
    }, 100);
  },

  stopProgressTimer() {
    clearInterval(this.timer);
    this.timer = null;
  },

  // ========== 播放列表管理 ==========
  loadPlaylist() {
    const data = localStorage.getItem(this.storageKey);
    this.playlist = data ? JSON.parse(data) : [];
    const index = localStorage.getItem(this.currentIndexKey);
    this.currentIndex = index ? parseInt(index) : 0;
  },

  savePlaylist() {
    localStorage.setItem(this.storageKey, JSON.stringify(this.playlist));
    localStorage.setItem(this.currentIndexKey, this.currentIndex.toString());
    // 触发播放列表更新事件，通知播放列表抽屉更新
    window.dispatchEvent(new CustomEvent('playlist-updated'));

    // 重新初始化 Lucide 图标（延迟执行以确保 DOM 更新完成）
    setTimeout(() => {
      createIcons({ icons });
    }, 10);
  },

  // ========== 音量管理 ==========
  loadVolume() {
    const savedVolume = localStorage.getItem(this.volumeKey);
    if (savedVolume !== null) {
      const volume = parseFloat(savedVolume);
      this.volume = volume;
      this.lastVolume = volume > 0 ? volume : this.lastVolume;
      this.isMuted = volume === 0;
      console.log('✅ 已加载音量设置:', volume);
    }
  },

  saveVolume() {
    localStorage.setItem(this.volumeKey, this.volume.toString());
  },

  // ========== 播放速度管理 ==========
  /**
   * 保存播放速度到 localStorage
   */
  savePlaybackRate() {
    localStorage.setItem(this.playbackRateKey, this.playbackRate.toString());
  },

  /**
   * 从 localStorage 加载播放速度
   */
  loadPlaybackRate() {
    const savedRate = localStorage.getItem(this.playbackRateKey);
    if (savedRate !== null) {
      const rate = parseFloat(savedRate);
      // 确保速率在可用范围内
      if (this.availableRates.includes(rate)) {
        this.playbackRate = rate;
        console.log('✅ 已加载播放速度设置:', rate);
      }
    }
  },

  // ========== 播放状态管理 ==========
  /**
   * 保存播放状态到 localStorage
   */
  savePlaybackState() {
    localStorage.setItem(this.currentTimeKey, this.currentTime.toString());
    localStorage.setItem(this.isPlayingKey, this.isPlaying.toString());
  },

  /**
   * 从 localStorage 加载播放状态
   */
  loadPlaybackState() {
    const savedTime = localStorage.getItem(this.currentTimeKey);
    const savedIsPlaying = localStorage.getItem(this.isPlayingKey);
    
    return {
      currentTime: savedTime ? parseFloat(savedTime) : 0,
      isPlaying: savedIsPlaying === 'true'
    };
  },

  /**
   * 清除播放状态
   */
  clearPlaybackState() {
    localStorage.removeItem(this.currentTimeKey);
    localStorage.removeItem(this.isPlayingKey);
  },

  /**
   * 添加节目到播放列表并立即播放
   * @param {Object} episode - 节目对象
   */
  addEpisode(episode) {
    const existingIndex = this.playlist.findIndex(item => item.id === episode.id);

    if (existingIndex !== -1) {
      // 已存在，切换到该节目并播放
      this.currentIndex = existingIndex;
      this.currentEpisode = episode;
      this.loadTrack(episode.audioUrl);
      this.play();
      this.savePlaylist();
      // 切换曲目后重置进度为0
      this.currentTime = 0;
      this.savePlaybackState();
      console.log('✅ 切换到已存在的节目:', episode.title);
      return;
    }

    // 添加到播放列表
    this.playlist.push(episode);
    this.currentIndex = this.playlist.length - 1;
    this.currentEpisode = episode;
    this.savePlaylist();

    // 加载并播放
    this.loadTrack(episode.audioUrl);
    this.play();
    
    // 新节目从头播放
    this.currentTime = 0;
    this.savePlaybackState();

    console.log('✅ 已添加到播放列表:', episode.title);
  },

  /**
   * 添加节目到播放列表但不播放（用于批量添加）
   * @param {Object} episode - 节目对象
   */
  addEpisodeToPlaylist(episode) {
    const existingIndex = this.playlist.findIndex(item => item.id === episode.id);

    if (existingIndex !== -1) {
      console.log('⏭️ 节目已存在:', episode.title);
      return false;
    }

    // 添加到播放列表
    this.playlist.push(episode);
    this.savePlaylist();

    return true;
  },
  removeEpisode(episodeId) {
    this.playlist = this.playlist.filter(item => item.id !== episodeId);
    if (this.currentIndex >= this.playlist.length) {
      this.currentIndex = Math.max(0, this.playlist.length - 1);
    }
    
    // 如果播放列表为空，停止播放并清空状态
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

  /**
   * 停止播放并清空所有状态
   */
  stopAndClear() {
    // 停止当前播放
    if (this.currentSound) {
      this.currentSound.stop();
      this.currentSound.unload();
      this.currentSound = null;
    }

    // 清理定时器
    this.stopProgressTimer();

    // 清理 AudioMotion
    if (this.audioMotion) {
      this.audioMotion.destroy();
      this.audioMotion = null;
    }

    // 清理 volumeGainNode
    if (this.volumeGainNode) {
      this.volumeGainNode.disconnect();
      this.volumeGainNode = null;
    }

    // 重置所有播放状态
    this.soundId = null;
    this.isPlaying = false;
    this.currentTime = 0;
    this.duration = 0;
    this.currentEpisode = null;
    
    // 清除保存的播放状态
    this.clearPlaybackState();
  },

  playNext() {
    if (this.playlist.length === 0) return;
    this.currentIndex = (this.currentIndex + 1) % this.playlist.length;
    const episode = this.playlist[this.currentIndex];
    this.currentEpisode = episode;
    this.loadTrack(episode.audioUrl);
    this.play();
    this.savePlaylist();
    // 切换曲目后重置进度为0
    this.currentTime = 0;
    this.savePlaybackState();
  },
  playPrevious() {
    if (this.playlist.length === 0) return;
    this.currentIndex = (this.currentIndex - 1 + this.playlist.length) % this.playlist.length;
    const episode = this.playlist[this.currentIndex];
    this.currentEpisode = episode;
    this.loadTrack(episode.audioUrl);
    this.play();
    this.savePlaylist();
    // 切换曲目后重置进度为0
    this.currentTime = 0;
    this.savePlaybackState();
  },

  playByIndex(index) {
    if (index >= 0 && index < this.playlist.length) {
      this.currentIndex = index;
      const episode = this.playlist[index];
      this.currentEpisode = episode;
      console.log('audio_url', episode.audioUrl);
      this.loadTrack(episode.audioUrl);
      this.play();
      this.savePlaylist();
      // 切换曲目后重置进度为0
      this.currentTime = 0;
      this.savePlaybackState();
    }
  },

  // ========== 工具方法 ==========
  formatTime(seconds) {
    // 处理 undefined, null, NaN 等情况
    if (!seconds || isNaN(seconds)) return '00:00';
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
  }
});


Alpine.start();




// 初始化 Swup (v4.x 版本)
const swup = new Swup({
  containers: ['#swup-main', '#swup-header'], // 指定要替换的容器
  animateHistoryBrowsing: true,
  plugins: [new SwupFormsPlugin()]
});

function init() {
  // 重新初始化 Lucide 图标
  createIcons({ icons });
}

// 页面首次加载
document.addEventListener('DOMContentLoaded', init);

// Swup v4.x 使用 hooks API
swup.hooks.on('content:replace', init);
