<?php

namespace App\View\Composers;

use App\Supports\Comment;
use Roots\Acorn\View\Composer;

class Comments extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'partials.comments',
    ];

    /**
     * The comment title.
     */
    public function title(): string
    {
        return sprintf(
            /* translators: %s: number of comments */
            _nx('One response', '%1$s responses', get_comments_number(), 'comments title', 'a-ripple-song'),
            number_format_i18n(get_comments_number())
        );
    }

    /**
     * Retrieve the comments.
     */
    public function responses(): ?string
    {
        if (! have_comments()) {
            return null;
        }

        return wp_list_comments([
            'style' => 'ol',
            'short_ping' => true,
            'callback' => [Comment::class, 'renderComment'],
            'avatar_size' => 24,
            'echo' => false,
        ]);
    }

    /**
     * The previous comments link.
     */
    public function previous(): ?string
    {
        if (! get_previous_comments_link()) {
            return null;
        }

        return get_previous_comments_link(
            __('Older comments', 'a-ripple-song')
        );
    }

    /**
     * The next comments link.
     */
    public function next(): ?string
    {
        if (! get_next_comments_link()) {
            return null;
        }

        return get_next_comments_link(
            __('Newer comments', 'a-ripple-song')
        );
    }

    /**
     * Determine if the comments are paginated.
     */
    public function paginated(): bool
    {
        return get_comment_pages_count() > 1 && get_option('page_comments');
    }

    /**
     * Determine if the comments are closed.
     */
    public function closed(): bool
    {
        return ! comments_open() && get_comments_number() != '0' && post_type_supports(get_post_type(), 'comments');
    }
}
