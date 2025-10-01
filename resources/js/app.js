import.meta.glob([
  '../images/**',
  '../fonts/**',
]);

import { createIcons, icons } from 'lucide';

document.addEventListener('DOMContentLoaded', () => {
  // 如果你想只包含某些图标以减小包体积
  createIcons({ icons: { Menu: icons.Menu, X: icons.X } });
  // 或者直接： createIcons({ icons });
});

// resources/scripts/app.js
import { Howl, Howler } from 'howler';

// 创建一个音频实例
const sound = new Howl({
  src: ['/wp-content/themes/your-theme-name/resources/audio/sound.mp3'], // ✅ 确保路径可访问
  volume: 0.8,
});

// 页面加载后自动播放
document.addEventListener("DOMContentLoaded", () => {
  sound.play();
});