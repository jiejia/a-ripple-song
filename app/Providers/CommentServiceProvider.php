<?php

namespace App\Providers;

use App\Supports\Comment;
use Illuminate\Support\ServiceProvider;

/**
 * Registers comment form and comment reply behavior.
 */
class CommentServiceProvider extends ServiceProvider
{
    /**
     * Register comment hooks.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueueCommentReply']);

        add_filter('comment_form_defaults', [Comment::class, 'filterCommentFormDefaults']);
        add_filter('comment_form_default_fields', [Comment::class, 'filterCommentFormDefaultFields']);
        add_filter('comment_form_field_comment', [Comment::class, 'filterCommentFormFieldComment']);
    }

    /**
     * Bootstrap comment services.
     *
     * @return void
     */
    public function boot(): void
    {
    }

    /**
     * Enqueue the built-in threaded comment reply script when needed.
     *
     * @return void
     */
    public function enqueueCommentReply(): void
    {
        if (! is_singular() || ! comments_open() || ! (bool) get_option('thread_comments')) {
            return;
        }

        wp_enqueue_script('comment-reply');
    }
}
