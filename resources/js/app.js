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
  current: 'retro',
  storageKey: 'aripplesong-theme',
  
  init() {
    // 从 localStorage 加载主题
    const savedTheme = localStorage.getItem(this.storageKey);
    if (savedTheme) {
      this.current = savedTheme;
    }
  },
  
  toggle() {
    this.current = this.current === 'retro' ? 'dim' : 'retro';
    localStorage.setItem(this.storageKey, this.current);
  },
  
  get isDark() {
    return this.current === 'dark';
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

// let audioMotion = null;

// var soundId = null;
// var lastSpectrumData = null; // 存储最后一次的频谱数据
// var timer = null; // 用于存储定时器ID

// class AudioPlayer {
//   constructor() {
//     this.currentSound = null;
//     this.soundId = null;
//     this.audioMotion = null;
//   }

//   loadTrack(audioUrl, options = {}) {
//     // 停止当前播放
//     if (this.currentSound) {
//       this.currentSound.stop();
//       this.currentSound.unload();
//       this.currentSound = null;
//     }

//     if (timer) {
//       clearInterval(timer);
//     }
//     soundId = null;
//     timer = null;

//     // 清理 AudioMotion
//     if (this.audioMotion) {
//       this.audioMotion.destroy();
//       this.audioMotion = null;
//     }


//     // 创建新的 Howl 实例
//     this.currentSound = new Howl({
//       src: [audioUrl],
//       loop: options.loop || false,
//       volume: options.volume || 1.0,
//       onplay: () => {
//         if (!this.audioMotion) {
//           this.audioMotion = new AudioMotionAnalyzer(
//             document.getElementById('wave'),
//             {
//               source: this.currentSound._sounds[0]._node,
//               connectSpeakers: true,
//               mode: 4,
//               alphaBars: false,
//               ansiBands: false,
//               barSpace: .25,
//               channelLayout: 'single',
//               colorMode: 'bar-level',
//               frequencyScale: 'log',
//               gradient: 'prism',
//               ledBars: false,
//               linearAmplitude: true,
//               linearBoost: 1.6,
//               lumiBars: false,
//               maxFreq: 16000,
//               minFreq: 30,
//               mirror: 0,
//               radial: false,
//               reflexRatio: .5,
//               reflexAlpha: 1,
//               roundBars: true,
//               showPeaks: false,
//               showScaleX: false,
//               smoothing: .7,
//               weightingFilter: 'D',
//               overlay: true,  // 添加这一行让背景透明，
//               showBgColor: false,
//               maxDecibels: -30
//             }
//           );
//         }

//       },
//       onload: () => {
//         this.updateDuration();
//       },
//       onend: () => {
//         if (options.onEnd) {
//           options.onEnd();
//         }
//       }
//     });

//     return this.currentSound;
//   }

//   // play() {
//   //   if (this.currentSound) {
//   //     this.soundId = this.currentSound.play();
//   //   }
//   // }

//   // pause() {
//   //   if (this.currentSound) {
//   //     this.currentSound.pause(this.soundId);
//   //   }
//   // }

//   updateDuration() {
//     const duration = this.currentSound.duration();
//     const minutes = Math.floor(duration / 60);
//     const seconds = Math.floor(duration % 60);
//     document.getElementById('sound-duration').textContent =
//       `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
//     document.getElementById('sound-progress').max = duration;
//   }
// }

// const player = new AudioPlayer();
// player.loadTrack('http://localhost:8888/podcast.m4a');

// // const sound = new Howl({
// //   src: ['http://localhost:8888/podcast.m4a'],
// //   loop: true,
// //   onplay: () => {
// //     if (!audioMotion) {
// //       // 等到开始播放时创建 AudioMotionAnalyzer
// //       audioMotion = new AudioMotionAnalyzer(
// //         document.getElementById('wave'),
// //         {
// //           source: sound._sounds[0]._node,
// //           connectSpeakers: true,
// //           mode: 4,
// //           alphaBars: false,
// //           ansiBands: false,
// //           barSpace: .25,
// //           channelLayout: 'single',
// //           colorMode: 'bar-level',
// //           frequencyScale: 'log',
// //           gradient: 'prism',
// //           ledBars: false,
// //           linearAmplitude: true,
// //           linearBoost: 1.6,
// //           lumiBars: false,
// //           maxFreq: 16000,
// //           minFreq: 30,
// //           mirror: 0,
// //           radial: false,
// //           reflexRatio: .5,
// //           reflexAlpha: 1,
// //           roundBars: true,
// //           showPeaks: false,
// //           showScaleX: false,
// //           smoothing: .7,
// //           weightingFilter: 'D',
// //           overlay: true,  // 添加这一行让背景透明，
// //           showBgColor: false,
// //           maxDecibels: -30

// //         }
// //       );
// //     } 

// //   },
// //   onpause: () => {
// //   }
// // });


// player.currentSound.on('load', () => {
//   const soundDuration = player.currentSound.duration();

//   // convert into mm:ss   
//   const minutes = Math.floor(player.currentSound.duration() / 60);
//   const seconds = Math.floor(player.currentSound.duration() % 60);
//   const soundDurationText = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

//   document.getElementById('sound-duration').textContent = soundDurationText;
//   document.getElementById('sound-progress').max = soundDuration;
// });




// /**
// * 播放或暂停音频
// */
// function playOrPause() {
//   var button = document.querySelector('#play-pause-button');

//   // 获取当前按钮的图标状态
//   var currentIcon = button.getAttribute('data-lucide');

//   if (player.currentSound.playing(player.soundId) == false) {
//     if (soundId === null) {
//       soundId = player.currentSound.play();
//     } else {
//       player.currentSound.play(soundId);
//     }
//     button.setAttribute('data-lucide', 'pause');
//     startTimer();
//   } else if (currentIcon === 'pause') {
//     player.currentSound.pause(soundId);
//     button.setAttribute('data-lucide', 'play');
//     stopTimer();
//   }

//   // 重新初始化 Lucide 图标以显示新的图标
//   createIcons({ icons });
// };

// function seek(pos) {
//   player.currentSound.seek(pos);
// }

// // 将函数暴露到全局作用域
// window.playOrPause = playOrPause;
// window.seek = seek;

// function startTimer() {
//   timer = setInterval(() => {
//     const pos = player.currentSound.seek(soundId) || 0;
//     document.getElementById('sound-progress').value = pos;

//     // 转换为 mm:ss 格式
//     const minutes = Math.floor(pos / 60);
//     const seconds = Math.floor(pos % 60);
//     const currentTimeText = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
//     document.getElementById('sound-current-time').textContent = currentTimeText;


//   }, 100);
// }

// function stopTimer() {
//   clearInterval(timer);
// }

// let volumeAutoCloseTimer = null;
// let lastVolume = 1; // 保存静音前的音量值
// let isMuted = false; // 静音状态

// function toggleVolumePanel() {
//   const volumePanel = document.getElementById('volume-panel');
//   const isHidden = volumePanel.classList.contains('hidden');

//   if (isHidden) {
//     // 打开面板
//     volumePanel.classList.remove('hidden');

//     // 启动自动关闭定时器
//     startVolumeAutoCloseTimer();

//     // 添加点击外部关闭的事件监听
//     setTimeout(() => {
//       document.addEventListener('click', closeVolumePanelOnClickOutside);
//     }, 0);
//   } else {
//     // 关闭面板
//     closeVolumePanel();
//   }
// }

// function closeVolumePanel() {
//   const volumePanel = document.getElementById('volume-panel');
//   volumePanel.classList.add('hidden');

//   // 清除定时器
//   if (volumeAutoCloseTimer) {
//     clearTimeout(volumeAutoCloseTimer);
//     volumeAutoCloseTimer = null;
//   }

//   // 移除点击外部关闭的事件监听
//   document.removeEventListener('click', closeVolumePanelOnClickOutside);
// }

// function closeVolumePanelOnClickOutside(event) {
//   const volumePanel = document.getElementById('volume-panel');
//   const volumeButton = document.getElementById('volume-button');

//   // 检查点击是否在面板或按钮之外
//   if (!volumePanel.contains(event.target) && !volumeButton.contains(event.target)) {
//     closeVolumePanel();
//   }
// }

// function startVolumeAutoCloseTimer() {
//   // 清除现有定时器
//   if (volumeAutoCloseTimer) {
//     clearTimeout(volumeAutoCloseTimer);
//   }

//   // 设置5秒后自动关闭
//   volumeAutoCloseTimer = setTimeout(() => {
//     closeVolumePanel();
//   }, 5000);
// }

// function changeVolume(value) {
//   const volume = parseFloat(value) / 300;
//   Howler.volume(volume);

//   // 更新静音状态和按钮
//   const muteCheckbox = document.getElementById('mute-checkbox');
//   const volumeButton = document.getElementById('volume-button');

//   if (volume === 0) {
//     isMuted = true;
//     if (muteCheckbox) muteCheckbox.checked = true;
//     if (volumeButton) {
//       volumeButton.setAttribute('data-lucide', 'volume-x');
//       createIcons({ icons });
//     }
//   } else {
//     isMuted = false;
//     if (muteCheckbox) muteCheckbox.checked = false;
//     if (volumeButton) {
//       volumeButton.setAttribute('data-lucide', 'volume-2');
//       createIcons({ icons });
//     }
//     lastVolume = volume; // 保存非零音量值
//   }

//   // 重置自动关闭定时器
//   startVolumeAutoCloseTimer();
// }

// function toggleMute() {
//   const volumeSlider = document.getElementById('volume-slider');
//   const muteCheckbox = document.getElementById('mute-checkbox');
//   const volumeButton = document.getElementById('volume-button');

//   if (isMuted) {
//     // 取消静音，恢复之前的音量
//     const restoreVolume = lastVolume > 0 ? lastVolume : 1;
//     Howler.volume(restoreVolume);
//     volumeSlider.value = restoreVolume * 300;
//     isMuted = false;
//     if (muteCheckbox) muteCheckbox.checked = false;
//     if (volumeButton) {
//       volumeButton.setAttribute('data-lucide', 'volume-2');
//       createIcons({ icons });
//     }
//   } else {
//     // 静音
//     lastVolume = Howler.volume(); // 保存当前音量
//     Howler.volume(0);
//     volumeSlider.value = 0;
//     isMuted = true;
//     if (muteCheckbox) muteCheckbox.checked = true;
//     if (volumeButton) {
//       volumeButton.setAttribute('data-lucide', 'volume-x');
//       createIcons({ icons });
//     }
//   }

//   // 重置自动关闭定时器
//   startVolumeAutoCloseTimer();
// }

// window.toggleVolumePanel = toggleVolumePanel;
// window.changeVolume = changeVolume;
// window.toggleMute = toggleMute;


// // ==================== 播放列表管理器 ====================
// class PlaylistManager {
//   constructor() {
//     this.storageKey = 'aripplesong-playlist';
//     this.currentIndexKey = 'aripplesong-current-index';
//   }

//   /**
//    * 获取完整播放列表
//    */
//   getPlaylist() {
//     const data = localStorage.getItem(this.storageKey);
//     return data ? JSON.parse(data) : [];
//   }

//   /**
//    * 保存播放列表
//    */
//   savePlaylist(playlist) {
//     localStorage.setItem(this.storageKey, JSON.stringify(playlist));
//     // 触发自定义事件，通知播放列表已更新
//     window.dispatchEvent(new CustomEvent('playlistUpdated'));
//   }

//   /**
//    * 添加单个节目到播放列表
//    * @param {Object} episode - 节目对象
//    * @param {number} episode.id - 节目ID
//    * @param {string} episode.audioUrl - MP3文件地址
//    * @param {string} episode.title - 标题
//    * @param {string} episode.description - 简介
//    * @param {string} episode.publishDate - 发布日期
//    * @param {string} episode.featuredImage - 特色图片地址
//    * @param {string} episode.link - 链接地址
//    */
//   addEpisode(episode) {
//     player.loadTrack(episode.audioUrl);
//     window.playOrPause();

//     console.log('currentIndex', this.getCurrentIndex());

//     const playlist = this.getPlaylist();

//     // 检查是否已存在（根据 ID）
//     const existingIndex = playlist.findIndex(item => item.id === episode.id);
//     this.setCurrentIndex(existingIndex);

//     this.renderPlaylist();

//     console.log('existingIndex', existingIndex);

//     if (existingIndex !== -1) {
//       console.log('该节目已在播放列表中');
//       return playlist;
//     }

//     // 添加到播放列表
//     playlist.push({
//       id: episode.id,
//       audioUrl: episode.audioUrl,
//       title: episode.title,
//       description: episode.description || '',
//       publishDate: episode.publishDate,
//       featuredImage: episode.featuredImage,
//       link: episode.link
//     });

//     this.savePlaylist(playlist);
//     console.log('已添加到播放列表:', episode.title);




//     return playlist;
//   }

//   /**
//    * 从播放列表中移除节目
//    * @param {number} episodeId - 节目ID
//    */
//   removeEpisode(episodeId) {
//     const playlist = this.getPlaylist();
//     const filtered = playlist.filter(item => item.id !== episodeId);
//     this.savePlaylist(filtered);
//     console.log('已从播放列表移除 ID:', episodeId);
//     return filtered;
//   }

//   /**
//    * 清空播放列表
//    */
//   clearPlaylist() {
//     localStorage.removeItem(this.storageKey);
//     localStorage.removeItem(this.currentIndexKey);
//     window.dispatchEvent(new CustomEvent('playlistUpdated'));
//     console.log('播放列表已清空');
//   }

//   /**
//    * 获取当前播放索引
//    */
//   getCurrentIndex() {
//     const index = localStorage.getItem(this.currentIndexKey);
//     return index ? parseInt(index) : 0;
//   }

//   /**
//    * 设置当前播放索引
//    */
//   setCurrentIndex(index) {
//     localStorage.setItem(this.currentIndexKey, index.toString());
//   }

//   /**
//    * 获取当前播放的节目
//    */
//   getCurrentEpisode() {
//     const playlist = this.getPlaylist();
//     const index = this.getCurrentIndex();
//     return playlist[index] || null;
//   }

//   /**
//    * 播放下一首
//    */
//   playNext() {
//     const playlist = this.getPlaylist();
//     if (playlist.length === 0) return null;

//     let index = this.getCurrentIndex();
//     index = (index + 1) % playlist.length;
//     this.setCurrentIndex(index);
//     return playlist[index];
//   }

//   /**
//    * 播放上一首
//    */
//   playPrevious() {
//     const playlist = this.getPlaylist();
//     if (playlist.length === 0) return null;

//     let index = this.getCurrentIndex();
//     index = (index - 1 + playlist.length) % playlist.length;
//     this.setCurrentIndex(index);
//     return playlist[index];
//   }

//   /**
//    * 获取播放列表统计信息
//    */
//   getStats() {
//     const playlist = this.getPlaylist();
//     // 这里暂时返回数量，时长需要在实际播放时计算
//     return {
//       count: playlist.length
//     };
//   }

//   /**
//    * 渲染播放列表到页面
//    */
//   renderPlaylist() {
//     const playlist = this.getPlaylist();
//     const playlistContainer = document.getElementById('playlist-container');
//     const playlistStats = document.getElementById('playlist-stats');

//     if (!playlistContainer) return;

//     // 更新统计信息
//     if (playlistStats) {
//       const stats = this.getStats();
//       playlistStats.textContent = `共 ${stats.count} 首`;
//     }

//     // 清空容器
//     playlistContainer.innerHTML = '';

//     // 如果播放列表为空
//     if (playlist.length === 0) {
//       playlistContainer.innerHTML = `
//         <div class="p-8 text-center text-base-content/60">
//           <i data-lucide="list-music" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
//           <p>播放列表为空</p>
//           <p class="text-sm mt-2">添加一些节目开始收听吧</p>
//         </div>
//       `;
//       createIcons({ icons });
//       return;
//     }

//     // 渲染播放列表项
//     const currentIndex = this.getCurrentIndex();
//     playlist.forEach((episode, index) => {
//       console.log('index', index);
//       const isPlaying = index === currentIndex;
//       const li = document.createElement('li');
//       li.className = `p-3 ${isPlaying ? 'bg-primary/10 border-l-4 border-primary' : 'hover:bg-base-200'} rounded-lg cursor-pointer transition-colors group`;
//       li.dataset.episodeId = episode.id;
//       li.dataset.episodeIndex = index;

//       li.innerHTML = `
//         <div class="flex gap-3 items-center">
//           <div class="relative flex-shrink-0">
//             <img src="${episode.featuredImage || 'https://via.placeholder.com/100'}" 
//                  alt="${episode.title}" 
//                  class="w-14 h-14 rounded object-cover" />
//             ${isPlaying ? `
//               <div class="absolute inset-0 flex items-center justify-center bg-black/30 rounded">
//                 <i data-lucide="volume-2" class="w-5 h-5 text-white"></i>
//               </div>
//             ` : ''}
//           </div>
//           <div class="flex-1 min-w-0">
//             <p class="font-semibold text-sm truncate ${isPlaying ? 'text-primary' : ''}">${episode.title}</p>
//             <p class="text-xs text-base-content/60">${episode.publishDate}</p>
//             ${episode.description ? `<p class="text-xs text-base-content/50 truncate">${episode.description}</p>` : ''}
//           </div>
//           <button 
//             onclick="window.playlistManager.removeEpisodeAndRender(${episode.id}); event.stopPropagation();"
//             class="btn btn-ghost btn-sm btn-circle opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0"
//             title="删除">
//             <i data-lucide="trash-2" class="w-4 h-4"></i>
//           </button>
//         </div>
//       `;

//       // 点击播放列表项时播放该节目
//       li.addEventListener('click', () => {
//         this.playEpisodeByIndex(index);
//       });

//       playlistContainer.appendChild(li);
//     });

//     // 重新初始化图标
//     createIcons({ icons });
//   }

//   /**
//    * 删除节目并重新渲染
//    */
//   removeEpisodeAndRender(episodeId) {
//     this.removeEpisode(episodeId);
//     this.renderPlaylist();
//   }

//   /**
//    * 根据索引播放节目
//    */
//   playEpisodeByIndex(index) {
//     this.setCurrentIndex(index);
//     const episode = this.getPlaylist()[index];
//     if (episode) {
//       console.log('播放节目:', episode.title);
//       // TODO: 这里需要集成实际的播放功能
//       // 可以触发一个自定义事件，让播放器响应
//       window.dispatchEvent(new CustomEvent('playEpisode', { detail: episode }));
//     }
//   }
// }

// // 创建全局播放列表管理器实例
// window.playlistManager = new PlaylistManager();

// // 监听播放列表更新事件，自动重新渲染
// window.addEventListener('playlistUpdated', () => {
//   if (window.playlistManager) {
//     window.playlistManager.renderPlaylist();
//   }
// });

// // 在页面初始化时渲染播放列表
// function initPlaylist() {
//   if (window.playlistManager) {
//     window.playlistManager.renderPlaylist();
//   }
// }

// // 页面加载完成后绘制波形
// document.addEventListener('DOMContentLoaded', function () {
//   initPlaylist();
// });

// Swup 页面切换后也要重新渲染播放列表
// swup.hooks.on('content:replace', initPlaylist);