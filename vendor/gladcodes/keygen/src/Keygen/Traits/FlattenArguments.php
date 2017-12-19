<?php

/*
 * This file is part of the Keygen package.
 *
 * (c) Glad Chinda <gladxeqs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Keygen\Traits;

trait FlattenArguments
{
	/**
	 * Flattens its arguments array into a simple array.
	 * 
	 * @return array
	 */
	protected function flattenArguments()
	{
		$args = func_get_args();
		$flat = [];

		foreach ($args as $arg) {
			if (is_array($arg)) {
				$flat = call_user_func_array(array($this, 'flattenArguments'), array_merge($flat, $arg));
				continue;
			}
			array_push($flat, $arg);
		}

		return $flat;
	}
}
