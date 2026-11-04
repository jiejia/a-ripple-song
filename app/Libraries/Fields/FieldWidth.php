<?php

namespace Jiejia\ARippleSong\Libraries\Fields;

defined('ABSPATH') || exit;

/**
 * Column width of a field in the form grid.
 */
enum FieldWidth: string
{
    case Full = 'full';
    case Half = 'half';
    case Third = 'third';
}
