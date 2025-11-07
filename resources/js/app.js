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

let audioMotion = null;

const sound = new Howl({
  src: ['http://localhost:8888/韩寒-奉献.flac'],
  loop: true,
  onplay: () => {
    if (!audioMotion) {
      // 等到开始播放时创建 AudioMotionAnalyzer
      audioMotion = new AudioMotionAnalyzer(
        document.getElementById('wave'),
        {
          source: sound._sounds[0]._node,
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
  onpause: () => {
  }
});


sound.on('load', () => {
  const soundDuration = sound.duration();

  // convert into mm:ss   
  const minutes = Math.floor(soundDuration / 60);
  const seconds = Math.floor(soundDuration % 60);
  const soundDurationText = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

  document.getElementById('sound-duration').textContent = soundDurationText;
  document.getElementById('sound-progress').max = soundDuration;
});


var soundId = null;
var lastSpectrumData = null; // 存储最后一次的频谱数据
var timer = null; // 用于存储定时器ID

/**
* 播放或暂停音频
*/
function playOrPause() {
  var button = document.querySelector('#play-pause-button');

  // 获取当前按钮的图标状态
  var currentIcon = button.getAttribute('data-lucide');

  if (currentIcon === 'play') {
    if (soundId === null) {
      soundId = sound.play();
    } else {
      sound.play(soundId);
    }
    button.setAttribute('data-lucide', 'pause');
    startTimer();
  } else if (currentIcon === 'pause') {
    sound.pause(soundId);
    button.setAttribute('data-lucide', 'play');
    stopTimer();
  }

  // 重新初始化 Lucide 图标以显示新的图标
  createIcons({ icons });
};

function seek(pos) {
  sound.seek(pos);
}

// 将函数暴露到全局作用域
window.playOrPause = playOrPause;
window.seek = seek;

function startTimer() {
  timer = setInterval(() => {
    const pos = sound.seek(soundId) || 0;
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




// 页面加载完成后绘制波形
document.addEventListener('DOMContentLoaded', function () {
});