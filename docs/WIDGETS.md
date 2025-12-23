# Widgets 目录

这个目录包含了主题的所有自定义 Widget 类文件。

## 文件结构

```
app/
├── widgets.php                    # 主入口文件（加载和注册所有 widgets）
└── Widgets/                       # Widget 类目录
    ├── AuthorsWidget.php          # 作者列表 Widget
    ├── BannerCarouselWidget.php   # 横幅轮播 Widget
    ├── BlogListWidget.php         # 博客列表 Widget
    ├── PodcastListWidget.php      # 播客列表 Widget
    ├── SubscribeLinksWidget.php   # 订阅链接 Widget
    └── TagsCloudWidget.php        # 标签云 Widget
```

## 如何添加新的 Widget

1. **创建 Widget 类文件**
   在 `app/Widgets/` 目录下创建新的 PHP 文件，例如 `MyCustomWidget.php`

2. **定义 Widget 类**
   ```php
   <?php
   
   /**
    * My Custom Widget
    * Widget 描述
    */
   class My_Custom_Widget extends WP_Widget {
       
       public function __construct() {
           parent::__construct(
               'my_custom_widget',
               __('aripplesong - 我的自定义 Widget', 'sage'),
               ['description' => __('Widget 描述', 'sage')]
           );
       }
       
       public function widget($args, $instance) {
           // Widget 前端显示逻辑
       }
       
       public function form($instance) {
           // Widget 后台表单
       }
       
       public function update($new_instance, $old_instance) {
           // Widget 保存逻辑
           return $instance;
       }
   }
   ```

3. **在主入口文件中注册**
   编辑 `app/widgets.php`，添加以下内容：
   
   ```php
   // 在 $widget_files 数组中添加
   $widget_files = [
       // ... 其他文件
       __DIR__ . '/Widgets/MyCustomWidget.php',
   ];
   
   // 在 widgets_init 钩子中注册
   add_action('widgets_init', function() {
       // ... 其他注册
       register_widget('My_Custom_Widget');
   });
   ```

## 优势

- ✅ **单一职责**: 每个文件只包含一个 Widget 类
- ✅ **易于维护**: 修改某个 Widget 不影响其他 Widget
- ✅ **团队协作**: 多人可以同时编辑不同的 Widget
- ✅ **代码清晰**: 不再是 1300+ 行的单一文件
- ✅ **按需加载**: 未来可以根据需要有条件地加载 Widget

## 注意事项

- 所有 Widget 类文件都会在 WordPress 初始化时自动加载
- Widget 类名必须使用下划线分隔（如 `My_Custom_Widget`）
- 文件名建议使用驼峰命名（如 `MyCustomWidget.php`）
- 修改后台管理脚本请在 `app/widgets.php` 的 `admin_enqueue_scripts` 钩子中添加

