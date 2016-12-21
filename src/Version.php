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
 * @method static Version V1()
 * @method static Version V2()
 */
class Version extends AbstractEnum
{
    const V1 = 'v1';
    const V2 = 'v2';
}
