import.meta.glob([
  '../images/**',
  '../fonts/**',
]);

import { createIcons, icons } from 'lucide';

document.addEventListener('DOMContentLoaded', () => {
  // 如果你想只包含某些图标以减小包体积
  // createIcons({ icons: { Menu: icons.Menu, X: icons.X } });
  createIcons({ icons });
});

// resources/scripts/app.js
import { Howl, Howler } from 'howler';
import AudioMotionAnalyzer from 'audiomotion-analyzer';

let audioMotion = null;

const sound = new Howl({
  src: ['http://localhost:8888/SoundHelix-Song-1.mp3'],
  loop: true,
  onplay: () => {
    if (!audioMotion) {
      // 等到开始播放时创建 AudioMotionAnalyzer
      audioMotion = new AudioMotionAnalyzer(
        document.getElementById('wave'),
        {
          source: sound._sounds[0]._node,
          connectSpeakers: true,
          mode: 5, // 添加这一行：使用 1/4 octave 模式（40个柱子）
          roundBars: true,
          colorMode: 'bar-level',
          showScaleX: false,
          showScaleY: false, // 隐藏左侧音量刻度
          gradient: 'prism', // 可选：设置渐变色方案，如 'rainbow', 'prism', 'classic' 等
          barSpace: 0.25, // 可选：条形图之间的间距 (0-1)
          showBgColor: false,
          overlay: true,
          bgAlpha: 0, // 可选：0-1 之间，0 为完全透明
          reflexRatio: 0,
        }
      );
    } else {
      audioMotion.play();
    }

  },
  onpause: () => {
    audioMotion.pause();
  }
});


sound.on('load', () => {
  const soundDuration = sound.duration();

  // convert into mm:ss   
  const minutes = Math.floor(soundDuration / 60);
  const seconds = Math.floor(soundDuration % 60);
  const soundDurationText = `${minutes}:${seconds.toString().padStart(2, '0')}`;

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

  }, 5);
}

function stopTimer() {
  clearInterval(timer);
}




// 页面加载完成后绘制波形
document.addEventListener('DOMContentLoaded', function () {
});