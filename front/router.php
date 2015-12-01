<?php
/**
 *	com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *	Bixie.nl
 *
 */

define('V_DETAILS','details');
define('V_CART','winkelwagen');
define('L_CHECKOUT','afrekenen');
define('CHECK_STAP','stap');
 
/**
 * @param	array	A named array
 * @return	array
 */
function BixmailingBuildRoute(&$query)
{
	$segments = array();

	// get a menu item based on Itemid or currently active
	$app		= JFactory::getApplication();
	$menu		= $app->getMenu();
	//$params		= JComponentHelper::getParams('com_bixmailing');
	//$advanced	= $params->get('sef_advanced_link', 0);
	// we need a menu item.  Either the one specified in the query, or the current active one if none specified
	if (empty($query['Itemid'])) {
		$menuItem = $menu->getActive();
		$menuItemGiven = false;
	} else {
		$menuItem = $menu->getItem($query['Itemid']);
		$menuItemGiven = true;
		if (isset($query['view']) && $menuItem->query['view'] != $query['view']) {
			$menuItem = $menu->getActive();
			$menuItemGiven = false;
		}
	}
	//$query['view'] = 'bixmailing';
	if (isset($query['productID'])) {
		$query['view'] = 'productdetails';
		$segments[] = V_DETAILS;
	}

	if (isset($query['view'])) {
		$view = $query['view'];
	} else {
		// we need to have a view in the query or it is an invalid URL
		return $segments;
	}

	// are we dealing with an product that is attached to a menu item?
	if (($menuItem instanceof stdClass) && $menuItem->query['view'] == $query['view'] && isset($query['productID']) && $menuItem->query['productID'] == intval($query['productID'])) {
		unset($query['view']);

		if (isset($query['layout'])) {
			unset($query['layout']);
		}

		unset($query['productID']);

		return $segments;
	}
	if ($view == 'bixmailing' || $view == 'productdetails') {
		if (!$menuItemGiven) {
			$segments[] = $view;
		}

		unset($query['view']);

		if ($view == 'productdetails') {
			if (isset($query['productID'])) {
				// Make sure we have the alias
//pr($query,$query['productID']);
// pr($segments);
				$db = JFactory::getDbo();
				$aquery = $db->setQuery($db->getQuery(true)
					->select('alias')
					->from('#__bps_product')
					->where('productID='.(int)$query['productID'])
				);
				$alias = $db->loadResult();
				$query['productID'] = $alias;
			} else {
				// we should have these two set for this view.  If we don't, it is an error
				return $segments;
			}
		}
		$catid = false;
		if (isset($query['catid'])) {
			$catid = $query['catid'];
		}

		/*if ($menuItemGiven && isset($menuItem->query['productID'])) {
			$mCatid = $menuItem->query['id'];
		} else {
			$mCatid = 0;
		}
*/
		if ($catid > 0) {
			$categories = JCategories::getInstance('Bixmailing');
			$category = $categories->get($catid);
	//pr($category);
			if ($category) {
				$path = array_reverse($category->getPath());

				$array = array();

				foreach($path as $id) {
					// if ((int)$id == (int)$mCatid) {
						// break;
					// }

					list($tmp, $id) = explode(':', $id, 2);

					$array[] = $id;
				}

				$array = array_reverse($array);

				// if (!$advanced && count($array)) {
					// $array[0] = (int)$catid.':'.$array[0];
				// }

				$segments = array_merge($segments, $array);
			}
		}

		if ($view == 'productdetails') {
			$segments[] = $query['productID'];
		}
		unset($query['productID']);
		unset($query['catid']);
	}
	if ($view == 'cart') {
		if (!$menuItemGiven) {
			$segments[] = $view;
		}
		unset($query['view']);
		if (isset($query['layout'])) {
			if ($query['layout'] == 'checkout') {
				$segments[] = L_CHECKOUT;
				if (isset($query['stap'])) {
					$segments[] = CHECK_STAP.$query['stap'];
				}
			}
			unset($query['layout']);
		}


	}
	return $segments;
}

/**
 * @param	array	A named array
 * @param	array
 *
 * Formats:
 *
 * index.php?/banners/task/id/Itemid
 *
 * index.php?/banners/id/Itemid
 */
function BixmailingParseRoute($segments)
{
	$vars = array();
	//layout als eerste?
	if ($segments[0] == L_CHECKOUT) {
		$vars['view'] = 'cart';
		$vars['layout'] = 'checkout';
		$stap = array_pop($segments);
		if (preg_match("/stap([2345])/",$stap,$match)) {
			$vars['stap'] = (int)$match[1];
		}
		return $vars;
	}
	// view is always the first element of the array
	$count = count($segments);
	if ($count)
	{
		$count--;
		$segment = array_shift($segments);
		if ($segment == V_DETAILS) {
			$vars['view'] = 'productdetails';
			//laatste het prodID
			$count--;
			$segment = array_pop($segments);
			if (is_numeric($segment)) {
				$vars['productID'] = $segment;
			} else {
				$db = JFactory::getDbo();
				$aquery = $db->setQuery($db->getQuery(true)
					->select('productID')
					->from('#__bps_product')
					->where('alias='.$db->Quote(str_replace(':','-',$segment)))
				);
				$vars['productID'] = $db->loadResult();
			}
		} else {
			if ($count)	{
				$count--;
				$segment = array_pop($segments) ;
			}
			if (is_numeric($segment)) {
				$vars['catid'] = $segment;
			} else {
				$db = JFactory::getDbo();
				$aquery = $db->setQuery($db->getQuery(true)
					->select('id')
					->from('#__categories')
					->where('alias='.$db->Quote($segment))
				);
				$vars['catid'] = $db->loadResult();
				return $vars;
			}
		}
	}
	//categorieen over
	if ($count)	{
		$count--;
		$segment = array_pop($segments) ;
		if (is_numeric($segment)) {
			$vars['catid'] = $segment;
		} else {
			$db = JFactory::getDbo();
			$aquery = $db->setQuery($db->getQuery(true)
				->select('id')
				->from('#__categories')
				->where('alias='.$db->Quote($segment))
			);
			$vars['catid'] = $db->loadResult();
			
		}
		
	}
	return $vars;
}
