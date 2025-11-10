import.meta.glob([
  '../images/**',
  '../fonts/**',
]);
import Swup from 'swup';
import { createIcons, icons } from 'lucide';
import { Howl, Howler } from 'howler';
import AudioMotionAnalyzer from 'audiomotion-analyzer';
import SwupFormsPlugin from '@swup/forms-plugin';


// 初始化 Swup (v4.x 版本)
const swup = new Swup({
  containers: ['#swup-main', '#swup-header'], // 指定要替换的容器
  animateHistoryBrowsing: true,
  plugins: [new SwupFormsPlugin()]
});

// 初始化函数 - 在页面加载和 Swup 切换后都会调用
function init() {
  // 重新初始化 Lucide 图标
  createIcons({ icons });

  // 其他需要在页面切换后重新初始化的代码可以放在这里
  console.log('页面初始化完成');
}

// 页面首次加载
document.addEventListener('DOMContentLoaded', init);

// Swup v4.x 使用 hooks API
// 在新内容加载完成后重新初始化
swup.hooks.on('content:replace', init);

// let audioMotion = null;

var soundId = null;
var lastSpectrumData = null; // 存储最后一次的频谱数据
var timer = null; // 用于存储定时器ID

class AudioPlayer {
  constructor() {
    this.currentSound = null;
    this.soundId = null;
    this.audioMotion = null;
  }

  loadTrack(audioUrl, options = {}) {
    // 停止当前播放
    if (this.currentSound) {
      this.currentSound.stop();
      this.currentSound.unload();
      this.currentSound = null;
    }

    if (timer) {
      clearInterval(timer);
    }
    soundId = null;
    timer = null;

    // 清理 AudioMotion
    if (this.audioMotion) {
      this.audioMotion.destroy();
      this.audioMotion = null;
    }


    // 创建新的 Howl 实例
    this.currentSound = new Howl({
      src: [audioUrl],
      loop: options.loop || false,
      volume: options.volume || 1.0,
      onplay: () => {
        if (!this.audioMotion) {
          this.audioMotion = new AudioMotionAnalyzer(
            document.getElementById('wave'),
            {
              source: this.currentSound._sounds[0]._node,
              connectSpeakers: true,
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
              overlay: true,  // 添加这一行让背景透明，
              showBgColor: false,
              maxDecibels: -30
            }
          );
        }

      },
      onload: () => {
        this.updateDuration();
      },
      onend: () => {
        if (options.onEnd) {
          options.onEnd();
        }
      }
    });

    return this.currentSound;
  }

  // play() {
  //   if (this.currentSound) {
  //     this.soundId = this.currentSound.play();
  //   }
  // }

  // pause() {
  //   if (this.currentSound) {
  //     this.currentSound.pause(this.soundId);
  //   }
  // }

  updateDuration() {
    const duration = this.currentSound.duration();
    const minutes = Math.floor(duration / 60);
    const seconds = Math.floor(duration % 60);
    document.getElementById('sound-duration').textContent =
      `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    document.getElementById('sound-progress').max = duration;
  }
}

const player = new AudioPlayer();
player.loadTrack('http://localhost:8888/podcast.m4a');

// const sound = new Howl({
//   src: ['http://localhost:8888/podcast.m4a'],
//   loop: true,
//   onplay: () => {
//     if (!audioMotion) {
//       // 等到开始播放时创建 AudioMotionAnalyzer
//       audioMotion = new AudioMotionAnalyzer(
//         document.getElementById('wave'),
//         {
//           source: sound._sounds[0]._node,
//           connectSpeakers: true,
//           mode: 4,
//           alphaBars: false,
//           ansiBands: false,
//           barSpace: .25,
//           channelLayout: 'single',
//           colorMode: 'bar-level',
//           frequencyScale: 'log',
//           gradient: 'prism',
//           ledBars: false,
//           linearAmplitude: true,
//           linearBoost: 1.6,
//           lumiBars: false,
//           maxFreq: 16000,
//           minFreq: 30,
//           mirror: 0,
//           radial: false,
//           reflexRatio: .5,
//           reflexAlpha: 1,
//           roundBars: true,
//           showPeaks: false,
//           showScaleX: false,
//           smoothing: .7,
//           weightingFilter: 'D',
//           overlay: true,  // 添加这一行让背景透明，
//           showBgColor: false,
//           maxDecibels: -30

//         }
//       );
//     } 

//   },
//   onpause: () => {
//   }
// });


player.currentSound.on('load', () => {
  const soundDuration = player.currentSound.duration();

  // convert into mm:ss   
  const minutes = Math.floor(player.currentSound.duration() / 60);
  const seconds = Math.floor(player.currentSound.duration() % 60);
  const soundDurationText = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

  document.getElementById('sound-duration').textContent = soundDurationText;
  document.getElementById('sound-progress').max = soundDuration;
});




/**
* 播放或暂停音频
*/
function playOrPause() {
  var button = document.querySelector('#play-pause-button');

  // 获取当前按钮的图标状态
  var currentIcon = button.getAttribute('data-lucide');

  if (player.currentSound.playing(player.soundId) == false) {
    if (soundId === null) {
      soundId = player.currentSound.play();
    } else {
      player.currentSound.play(soundId);
    }
    button.setAttribute('data-lucide', 'pause');
    startTimer();
  } else if (currentIcon === 'pause') {
    player.currentSound.pause(soundId);
    button.setAttribute('data-lucide', 'play');
    stopTimer();
  }

  // 重新初始化 Lucide 图标以显示新的图标
  createIcons({ icons });
};

function seek(pos) {
  player.currentSound.seek(pos);
}

// 将函数暴露到全局作用域
window.playOrPause = playOrPause;
window.seek = seek;

function startTimer() {
  timer = setInterval(() => {
    const pos = player.currentSound.seek(soundId) || 0;
    document.getElementById('sound-progress').value = pos;

    // 转换为 mm:ss 格式
    const minutes = Math.floor(pos / 60);
    const seconds = Math.floor(pos % 60);
    const currentTimeText = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    document.getElementById('sound-current-time').textContent = currentTimeText;


  }, 100);
}

function stopTimer() {
  clearInterval(timer);
}

let volumeAutoCloseTimer = null;
let lastVolume = 1; // 保存静音前的音量值
let isMuted = false; // 静音状态

function toggleVolumePanel() {
  const volumePanel = document.getElementById('volume-panel');
  const isHidden = volumePanel.classList.contains('hidden');

  if (isHidden) {
    // 打开面板
    volumePanel.classList.remove('hidden');

    // 启动自动关闭定时器
    startVolumeAutoCloseTimer();

    // 添加点击外部关闭的事件监听
    setTimeout(() => {
      document.addEventListener('click', closeVolumePanelOnClickOutside);
    }, 0);
  } else {
    // 关闭面板
    closeVolumePanel();
  }
}

function closeVolumePanel() {
  const volumePanel = document.getElementById('volume-panel');
  volumePanel.classList.add('hidden');

  // 清除定时器
  if (volumeAutoCloseTimer) {
    clearTimeout(volumeAutoCloseTimer);
    volumeAutoCloseTimer = null;
  }

  // 移除点击外部关闭的事件监听
  document.removeEventListener('click', closeVolumePanelOnClickOutside);
}

function closeVolumePanelOnClickOutside(event) {
  const volumePanel = document.getElementById('volume-panel');
  const volumeButton = document.getElementById('volume-button');

  // 检查点击是否在面板或按钮之外
  if (!volumePanel.contains(event.target) && !volumeButton.contains(event.target)) {
    closeVolumePanel();
  }
}

function startVolumeAutoCloseTimer() {
  // 清除现有定时器
  if (volumeAutoCloseTimer) {
    clearTimeout(volumeAutoCloseTimer);
  }

  // 设置5秒后自动关闭
  volumeAutoCloseTimer = setTimeout(() => {
    closeVolumePanel();
  }, 5000);
}

function changeVolume(value) {
  const volume = parseFloat(value) / 300;
  Howler.volume(volume);

  // 更新静音状态和按钮
  const muteCheckbox = document.getElementById('mute-checkbox');
  const volumeButton = document.getElementById('volume-button');

  if (volume === 0) {
    isMuted = true;
    if (muteCheckbox) muteCheckbox.checked = true;
    if (volumeButton) {
      volumeButton.setAttribute('data-lucide', 'volume-x');
      createIcons({ icons });
    }
  } else {
    isMuted = false;
    if (muteCheckbox) muteCheckbox.checked = false;
    if (volumeButton) {
      volumeButton.setAttribute('data-lucide', 'volume-2');
      createIcons({ icons });
    }
    lastVolume = volume; // 保存非零音量值
  }

  // 重置自动关闭定时器
  startVolumeAutoCloseTimer();
}

function toggleMute() {
  const volumeSlider = document.getElementById('volume-slider');
  const muteCheckbox = document.getElementById('mute-checkbox');
  const volumeButton = document.getElementById('volume-button');

  if (isMuted) {
    // 取消静音，恢复之前的音量
    const restoreVolume = lastVolume > 0 ? lastVolume : 1;
    Howler.volume(restoreVolume);
    volumeSlider.value = restoreVolume * 300;
    isMuted = false;
    if (muteCheckbox) muteCheckbox.checked = false;
    if (volumeButton) {
      volumeButton.setAttribute('data-lucide', 'volume-2');
      createIcons({ icons });
    }
  } else {
    // 静音
    lastVolume = Howler.volume(); // 保存当前音量
    Howler.volume(0);
    volumeSlider.value = 0;
    isMuted = true;
    if (muteCheckbox) muteCheckbox.checked = true;
    if (volumeButton) {
      volumeButton.setAttribute('data-lucide', 'volume-x');
      createIcons({ icons });
    }
  }

  // 重置自动关闭定时器
  startVolumeAutoCloseTimer();
}

window.toggleVolumePanel = toggleVolumePanel;
window.changeVolume = changeVolume;
window.toggleMute = toggleMute;


// ==================== 播放列表管理器 ====================
class PlaylistManager {
  constructor() {
    this.storageKey = 'aripplesong-playlist';
    this.currentIndexKey = 'aripplesong-current-index';
  }

  /**
   * 获取完整播放列表
   */
  getPlaylist() {
    const data = localStorage.getItem(this.storageKey);
    return data ? JSON.parse(data) : [];
  }

  /**
   * 保存播放列表
   */
  savePlaylist(playlist) {
    localStorage.setItem(this.storageKey, JSON.stringify(playlist));
    // 触发自定义事件，通知播放列表已更新
    window.dispatchEvent(new CustomEvent('playlistUpdated'));
  }

  /**
   * 添加单个节目到播放列表
   * @param {Object} episode - 节目对象
   * @param {number} episode.id - 节目ID
   * @param {string} episode.audioUrl - MP3文件地址
   * @param {string} episode.title - 标题
   * @param {string} episode.description - 简介
   * @param {string} episode.publishDate - 发布日期
   * @param {string} episode.featuredImage - 特色图片地址
   * @param {string} episode.link - 链接地址
   */
  addEpisode(episode) {
    player.loadTrack(episode.audioUrl);
    window.playOrPause();

    console.log('currentIndex', this.getCurrentIndex());

    const playlist = this.getPlaylist();

    // 检查是否已存在（根据 ID）
    const existingIndex = playlist.findIndex(item => item.id === episode.id);
    this.setCurrentIndex(existingIndex);

    this.renderPlaylist();

    console.log('existingIndex', existingIndex);

    if (existingIndex !== -1) {
      console.log('该节目已在播放列表中');
      return playlist;
    }

    // 添加到播放列表
    playlist.push({
      id: episode.id,
      audioUrl: episode.audioUrl,
      title: episode.title,
      description: episode.description || '',
      publishDate: episode.publishDate,
      featuredImage: episode.featuredImage,
      link: episode.link
    });

    this.savePlaylist(playlist);
    console.log('已添加到播放列表:', episode.title);




    return playlist;
  }

  /**
   * 从播放列表中移除节目
   * @param {number} episodeId - 节目ID
   */
  removeEpisode(episodeId) {
    const playlist = this.getPlaylist();
    const filtered = playlist.filter(item => item.id !== episodeId);
    this.savePlaylist(filtered);
    console.log('已从播放列表移除 ID:', episodeId);
    return filtered;
  }

  /**
   * 清空播放列表
   */
  clearPlaylist() {
    localStorage.removeItem(this.storageKey);
    localStorage.removeItem(this.currentIndexKey);
    window.dispatchEvent(new CustomEvent('playlistUpdated'));
    console.log('播放列表已清空');
  }

  /**
   * 获取当前播放索引
   */
  getCurrentIndex() {
    const index = localStorage.getItem(this.currentIndexKey);
    return index ? parseInt(index) : 0;
  }

  /**
   * 设置当前播放索引
   */
  setCurrentIndex(index) {
    localStorage.setItem(this.currentIndexKey, index.toString());
  }

  /**
   * 获取当前播放的节目
   */
  getCurrentEpisode() {
    const playlist = this.getPlaylist();
    const index = this.getCurrentIndex();
    return playlist[index] || null;
  }

  /**
   * 播放下一首
   */
  playNext() {
    const playlist = this.getPlaylist();
    if (playlist.length === 0) return null;

    let index = this.getCurrentIndex();
    index = (index + 1) % playlist.length;
    this.setCurrentIndex(index);
    return playlist[index];
  }

  /**
   * 播放上一首
   */
  playPrevious() {
    const playlist = this.getPlaylist();
    if (playlist.length === 0) return null;

    let index = this.getCurrentIndex();
    index = (index - 1 + playlist.length) % playlist.length;
    this.setCurrentIndex(index);
    return playlist[index];
  }

  /**
   * 获取播放列表统计信息
   */
  getStats() {
    const playlist = this.getPlaylist();
    // 这里暂时返回数量，时长需要在实际播放时计算
    return {
      count: playlist.length
    };
  }

  /**
   * 渲染播放列表到页面
   */
  renderPlaylist() {
    const playlist = this.getPlaylist();
    const playlistContainer = document.getElementById('playlist-container');
    const playlistStats = document.getElementById('playlist-stats');

    if (!playlistContainer) return;

    // 更新统计信息
    if (playlistStats) {
      const stats = this.getStats();
      playlistStats.textContent = `共 ${stats.count} 首`;
    }

    // 清空容器
    playlistContainer.innerHTML = '';

    // 如果播放列表为空
    if (playlist.length === 0) {
      playlistContainer.innerHTML = `
        <div class="p-8 text-center text-base-content/60">
          <i data-lucide="list-music" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
          <p>播放列表为空</p>
          <p class="text-sm mt-2">添加一些节目开始收听吧</p>
        </div>
      `;
      createIcons({ icons });
      return;
    }

    // 渲染播放列表项
    const currentIndex = this.getCurrentIndex();
    playlist.forEach((episode, index) => {
      console.log('index', index);
      const isPlaying = index === currentIndex;
      const li = document.createElement('li');
      li.className = `p-3 ${isPlaying ? 'bg-primary/10 border-l-4 border-primary' : 'hover:bg-base-200'} rounded-lg cursor-pointer transition-colors group`;
      li.dataset.episodeId = episode.id;
      li.dataset.episodeIndex = index;

      li.innerHTML = `
        <div class="flex gap-3 items-center">
          <div class="relative flex-shrink-0">
            <img src="${episode.featuredImage || 'https://via.placeholder.com/100'}" 
                 alt="${episode.title}" 
                 class="w-14 h-14 rounded object-cover" />
            ${isPlaying ? `
              <div class="absolute inset-0 flex items-center justify-center bg-black/30 rounded">
                <i data-lucide="volume-2" class="w-5 h-5 text-white"></i>
              </div>
            ` : ''}
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-sm truncate ${isPlaying ? 'text-primary' : ''}">${episode.title}</p>
            <p class="text-xs text-base-content/60">${episode.publishDate}</p>
            ${episode.description ? `<p class="text-xs text-base-content/50 truncate">${episode.description}</p>` : ''}
          </div>
          <button 
            onclick="window.playlistManager.removeEpisodeAndRender(${episode.id}); event.stopPropagation();"
            class="btn btn-ghost btn-sm btn-circle opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0"
            title="删除">
            <i data-lucide="trash-2" class="w-4 h-4"></i>
          </button>
        </div>
      `;

      // 点击播放列表项时播放该节目
      li.addEventListener('click', () => {
        this.playEpisodeByIndex(index);
      });

      playlistContainer.appendChild(li);
    });

    // 重新初始化图标
    createIcons({ icons });
  }

  /**
   * 删除节目并重新渲染
   */
  removeEpisodeAndRender(episodeId) {
    this.removeEpisode(episodeId);
    this.renderPlaylist();
  }

  /**
   * 根据索引播放节目
   */
  playEpisodeByIndex(index) {
    this.setCurrentIndex(index);
    const episode = this.getPlaylist()[index];
    if (episode) {
      console.log('播放节目:', episode.title);
      // TODO: 这里需要集成实际的播放功能
      // 可以触发一个自定义事件，让播放器响应
      window.dispatchEvent(new CustomEvent('playEpisode', { detail: episode }));
    }
  }
}

// 创建全局播放列表管理器实例
window.playlistManager = new PlaylistManager();

// 监听播放列表更新事件，自动重新渲染
window.addEventListener('playlistUpdated', () => {
  if (window.playlistManager) {
    window.playlistManager.renderPlaylist();
  }
});

// 在页面初始化时渲染播放列表
function initPlaylist() {
  if (window.playlistManager) {
    window.playlistManager.renderPlaylist();
  }
}

// 页面加载完成后绘制波形
document.addEventListener('DOMContentLoaded', function () {
  initPlaylist();
});

// Swup 页面切换后也要重新渲染播放列表
swup.hooks.on('content:replace', initPlaylist);