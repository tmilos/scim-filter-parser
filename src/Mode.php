<?php

/*
 * This file is part of the tmilos/scim-filter-parser package.
 *
 * (c) Milos Tomic <tmilos@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Tmilos\ScimFilterParser;

use Tmilos\Value\AbstractEnum;

/**
 * @method static Mode FILTER()
 * @method static Mode PATH()
 */
class Mode extends AbstractEnum
{
    const FILTER = 'filter';
    const PATH = 'path';
}
