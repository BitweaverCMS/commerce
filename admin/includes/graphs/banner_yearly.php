<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                                 |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//  $Id: banner_yearly.php,v 1.2 2005/10/06 21:01:45 spiderr Exp $
//

  include(DIR_WS_CLASSES . 'phplot.php');

  $stats = array(array('0', '0', '0'));
  $banner_stats = $db->Execute("select year(banners_history_date) as year,
                                       sum(banners_shown) as value, sum(banners_clicked) as dvalue
                                from " . TABLE_BANNERS_HISTORY . "
                                where banners_id = '" . $banner_id . "' group by year");

  while (!$banner_stats->EOF) {
    $stats[] = array($banner_stats->fields['year'], (($banner_stats->fields['value']) ? $banner_stats->fields['value'] : '0'), (($banner_stats->fields['dvalue']) ? $banner_stats->fields['dvalue'] : '0'));
    $banner_stats->MoveNext();
  }

  $graph = new PHPlot(600, 350, 'images/graphs/banner_yearly-' . $banner_id . '.' . $banner_extension);

  $graph->SetFileFormat($banner_extension);
  $graph->SetIsInline(1);
  $graph->SetPrintImage(0);

  $graph->SetSkipBottomTick(1);
  $graph->SetDrawYGrid(1);
  $graph->SetPrecisionY(0);
  $graph->SetPlotType('lines');

  $graph->SetPlotBorderType('left');
  $graph->SetTitleFontSize('4');
  $graph->SetTitle(sprintf(TEXT_BANNERS_YEARLY_STATISTICS, $banner['banners_title']));

  $graph->SetBackgroundColor('white');

  $graph->SetVertTickPosition('plotleft');
  $graph->SetDataValues($stats);
  $graph->SetDataColors(array('blue','red'),array('blue', 'red'));

  $graph->DrawGraph();

  $graph->PrintImage();
?>