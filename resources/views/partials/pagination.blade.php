@php
    /**
     * Build pagination links for archive-style list pages.
     */
    $paginationLinks = paginate_links([
        'type' => 'array',
        'mid_size' => 1,
        'prev_next' => true,
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
    ]);
@endphp

@if (!empty($paginationLinks))
    <nav class="mt-6 flex justify-center">
        <div class="join">
            @foreach ($paginationLinks as $paginationLink)
                @php
                    /**
                     * Convert core pagination markup into daisyUI buttons.
                     */
                    $paginationClasses = 'join-item btn btn-sm md:btn-md border-base-300';
                    $defaultPaginationClasses = $paginationClasses . ' bg-base-100 hover:bg-base-200';
                    $currentPaginationClasses = $paginationClasses . ' btn-primary border-primary text-primary-content pointer-events-none';

                    if (str_contains($paginationLink, 'dots')) {
                        $paginationLink = sprintf(
                            '<span class="%s pointer-events-none">%s</span>',
                            esc_attr($defaultPaginationClasses . ' btn-disabled'),
                            esc_html(wp_strip_all_tags($paginationLink))
                        );
                    } elseif (str_contains($paginationLink, 'current')) {
                        $paginationLink = preg_replace(
                            '/class="([^"]*)"/',
                            'class="' . esc_attr($currentPaginationClasses) . '"',
                            $paginationLink,
                            1
                        );
                    } else {
                        $paginationLink = preg_replace(
                            '/class="([^"]*)"/',
                            'class="' . esc_attr($defaultPaginationClasses) . '"',
                            $paginationLink,
                            1
                        );
                    }
                @endphp

                {!! $paginationLink !!}
            @endforeach
        </div>
    </nav>
@endif
