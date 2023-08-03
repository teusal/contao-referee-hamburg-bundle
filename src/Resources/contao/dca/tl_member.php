<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

 /*
  * change callbacks, disable newsletter synchronization by contao newsletter-bundle
  */
unset($GLOBALS['TL_DCA']['tl_member']['config']['onload_callback']['Newsletter'],
      $GLOBALS['TL_DCA']['tl_member']['fields']['disable']['save_callback']['Newsletter'],
      $GLOBALS['TL_DCA']['tl_member']['fields']['newsletter']['save_callback']['Newsletter']);
