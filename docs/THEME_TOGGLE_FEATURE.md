# 主题切换功能说明

## 功能概述
主题切换支持三种模式的循环切换：
1. **明亮模式** (Light) - 使用 `retro` 主题
2. **黑暗模式** (Dark) - 使用 `dim` 主题  
3. **跟随系统** (Auto) - 自动根据系统偏好切换

## 切换顺序
点击主题切换按钮时，按以下顺序循环：
```
明亮 → 黑暗 → 跟随系统 → 明亮 → ...
```

## 图标说明
- 🌞 **sun** - 明亮模式
- 🌙 **moon** - 黑暗模式
- 🌗 **sun-moon** - 跟随系统模式

## 技术实现

### JavaScript (app.js)
使用 Alpine.js Store 管理主题状态：

```javascript
Alpine.store('theme', {
  mode: 'auto', // 'light', 'dark', 'auto'
  storageKey: 'aripplesong-theme-mode',
  lightTheme: 'retro',
  darkTheme: 'dim',
  
  // 初始化：从 localStorage 加载，监听系统主题变化
  init() { ... }
  
  // 切换模式：light -> dark -> auto -> light
  toggle() { ... }
  
  // 应用主题到 DOM
  applyTheme() { ... }
})
```

### 模板 (header.blade.php)
使用 Alpine.js 指令实现响应式图标切换：

```html
<button @click="$store.theme.toggle()">
  <i data-lucide="sun" x-show="$store.theme.isLight"></i>
  <i data-lucide="moon" x-show="$store.theme.isDark && !$store.theme.isAuto"></i>
  <i data-lucide="sun-moon" x-show="$store.theme.isAuto"></i>
</button>
```

## 特性
- ✅ 状态持久化（localStorage）
- ✅ 自动监听系统主题变化
- ✅ 平滑过渡动画
- ✅ 响应式图标显示
- ✅ 工具提示显示当前模式

## 测试方法
1. 点击主题切换按钮，观察图标和主题变化
2. 在"跟随系统"模式下，更改系统主题设置，观察主题自动切换
3. 刷新页面，确认主题设置被正确保存和恢复

