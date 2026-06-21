<?php

namespace App\Supports;

/**
 * Comment rendering and comment form customization helpers.
 */
class Comment
{
    /**
     * Render a single comment item with DaisyUI-friendly markup.
     *
     * @param \WP_Comment $comment Comment object.
     * @param array<string, mixed> $args Comment arguments.
     * @param int $depth Comment depth level.
     * @return void
     */
    public static function renderComment($comment, array $args, int $depth): void
    {
        ?>
        <li id="comment-<?php comment_ID(); ?>" <?php comment_class('comment-item'); ?>>
            <article class="rounded-lg bg-base-200/50 p-4 transition-shadow hover:shadow-sm">
                <div class="flex gap-2">
                    <div class="flex-shrink-0">
                        <?php if ((int) ($args['avatar_size'] ?? 0) !== 0) : ?>
                            <div class="avatar">
                                <div class="h-6 w-6 rounded-full ring ring-primary ring-offset-1 ring-offset-base-100">
                                    <?php echo get_avatar($comment, (int) ($args['avatar_size'] ?? 24)); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="text-sm font-bold">
                                <?php echo wp_kses_post(get_comment_author_link($comment)); ?>
                            </span>

                            <?php if ((int) $comment->user_id === (int) get_post_field('post_author', get_the_ID())) : ?>
                                <span class="badge badge-primary badge-sm"><?php esc_html_e('Author', 'sage'); ?></span>
                            <?php endif; ?>

                            <span class="flex items-center gap-1 text-xs text-base-content/60">
                                <i data-lucide="clock" class="h-4 w-4"></i>
                                <?php echo esc_html(get_localized_comment_date($comment)); ?>
                            </span>

                            <?php if ((string) $comment->comment_approved === '0') : ?>
                                <span class="badge badge-warning badge-sm"><?php esc_html_e('Pending Approval', 'sage'); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="comment-content mb-3 text-sm leading-relaxed text-base-content/80">
                            <?php comment_text(); ?>
                        </div>

                        <div class="flex items-center gap-3">
                            <?php
                            comment_reply_link(array_merge($args, [
                                'add_below' => 'comment',
                                'depth' => $depth,
                                'max_depth' => $args['max_depth'],
                                'before' => '<button class="btn btn-ghost btn-sm gap-1 text-sm">',
                                'after' => '</button>',
                                'reply_text' => '<i data-lucide="reply" class="h-4 w-4"></i> ' . __('Reply', 'sage'),
                            ]));
                            ?>

                            <?php
                            edit_comment_link(
                                '<i data-lucide="pencil" class="h-4 w-4"></i> ' . __('Edit', 'sage'),
                                '<button class="btn btn-ghost btn-sm gap-1 text-sm">',
                                '</button>'
                            );
                            ?>
                        </div>
                    </div>
                </div>
            </article>
        <?php
    }

    /**
     * Customize comment form defaults with DaisyUI styling.
     *
     * @param array<string, mixed> $defaults Comment form defaults.
     * @return array<string, mixed>
     */
    public static function filterCommentFormDefaults(array $defaults): array
    {
        $defaults['class_form'] = 'space-y-4';
        $defaults['class_submit'] = 'btn btn-primary btn-sm gap-2 text-sm';
        $defaults['submit_button'] = '<button type="submit" id="%2$s" class="%3$s">%4$s <i data-lucide="send" class="h-4 w-4"></i></button>';
        $defaults['title_reply_before'] = '<h3 id="reply-title" class="mb-4 hidden text-md font-bold">';
        $defaults['title_reply_after'] = '</h3>';
        $defaults['cancel_reply_before'] = '<div class="text-sm">';
        $defaults['cancel_reply_after'] = '</div>';
        $defaults['cancel_reply_link'] = '<button type="button" class="btn btn-ghost btn-sm gap-1 text-sm"><i data-lucide="x" class="h-4 w-4"></i> %s</button>';
        $defaults['comment_notes_before'] = '<p class="comment-notes text-sm text-base-content/60">' . esc_html__('Your email address will not be published.', 'sage') . '</p>';
        $defaults['comment_notes_after'] = '';
        $defaults['logged_in_as'] = '<p class="logged-in-as text-sm text-base-content/60">' .
            sprintf(
                wp_kses_post(__('Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s">Log out?</a>', 'sage')),
                esc_url((string) get_edit_user_link()),
                esc_html(wp_get_current_user()->display_name),
                esc_url((string) wp_logout_url(get_permalink()))
            ) .
        '</p>';

        return $defaults;
    }

    /**
     * Customize comment form fields with DaisyUI styling.
     *
     * @param array<string, string> $fields Comment form fields.
     * @return array<string, string>
     */
    public static function filterCommentFormDefaultFields(array $fields): array
    {
        /** @var array<string, string> $commenter Current commenter values. */
        $commenter = wp_get_current_commenter();

        /** @var bool $isNameEmailRequired Whether name and email are required. */
        $isNameEmailRequired = (bool) get_option('require_name_email');

        /** @var string $requiredAttribute HTML required attribute. */
        $requiredAttribute = $isNameEmailRequired ? ' required' : '';

        $fields['author'] = '<div class="form-control"><label class="label" for="author"><span class="label-text text-sm">' . esc_html__('Name', 'sage') . ' <span class="text-error">*</span></span></label><input type="text" id="author" name="author" value="' . esc_attr($commenter['comment_author'] ?? '') . '" class="input input-bordered w-full text-sm"' . $requiredAttribute . ' /></div>';

        $fields['email'] = '<div class="form-control"><label class="label" for="email"><span class="label-text text-sm">' . esc_html__('Email', 'sage') . ' <span class="text-error">*</span></span></label><input type="email" id="email" name="email" value="' . esc_attr($commenter['comment_author_email'] ?? '') . '" class="input input-bordered w-full text-sm"' . $requiredAttribute . ' /></div>';

        $fields['url'] = '<div class="form-control"><label class="label" for="url"><span class="label-text text-sm">' . esc_html__('Website', 'sage') . '</span></label><input type="url" id="url" name="url" value="' . esc_attr($commenter['comment_author_url'] ?? '') . '" class="input input-bordered w-full text-sm" /></div>';

        $fields['cookies'] = '<div class="form-control"><label class="comment-form-cookies-consent flex items-start gap-2"><input type="checkbox" id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" value="yes" class="checkbox checkbox-sm mt-1" /><span class="label-text text-sm leading-relaxed">' . esc_html__('Save my name, email, and website in this browser for the next time I comment.', 'sage') . '</span></label></div>';

        return $fields;
    }

    /**
     * Customize the main comment textarea field with DaisyUI styling.
     *
     * @param string $field Comment textarea field HTML.
     * @return string
     */
    public static function filterCommentFormFieldComment(string $field): string
    {
        return '<div class="form-control"><label class="label" for="comment"><span class="label-text text-sm">' . esc_html__('Comment', 'sage') . ' <span class="text-error">*</span></span></label><textarea id="comment" name="comment" rows="6" class="textarea textarea-bordered w-full text-sm" required></textarea></div>';
    }
}
