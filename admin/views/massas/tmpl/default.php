<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');

$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
?>

<form action="<?php echo JRoute::_('index.php?option=com_bixmailing&view=massas'); ?>" method="post" name="adminForm"
	  id="adminForm" class="uk-form">
	<div class="uk-grid">
		<div class="uk-width-1-1">
			<div class="uk-panel uk-panel-box">
				<div id="filter-bar">
					<div class="uk-grid">
						<div class="uk-width-1-2 filter-search">
							<label class="filter-search-lbl"
								   for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
							<input type="text" name="filter_search" id="filter_search"
								   value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
								   title="<?php echo JText::_('Search'); ?>"/>
							<button class="uk-button uk-button-primary" type="submit"><i
									class="uk-icon-search uk-margin-small-right"></i><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
							</button>
							<button class="uk-button" type="button"
									onclick="document.id('filter_search').value='';this.form.submit();"><i
									class="uk-icon-ban uk-margin-small-right"></i><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
							</button>
						</div>
						<div class="uk-width-1-2 filter-select uk-text-right">
							<select name="filter_published" class="inputbox" onchange="this.form.submit()">
								<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
								<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), "value", "text", $this->state->get('filter.state'), true); ?>
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="uk-grid">
		<div class="uk-width-1-1">
			<div class="uk-panel uk-panel-box">
				<table class="uk-table uk-table-hover uk-table-striped uk-table-condensed">
					<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)"/>
						</th>

						<th align="left">
							<?php echo JHtml::_('grid.sort', 'COM_BIXMAILING_MASSAMAIL_NAAM', 'm.naam', $listDirn, $listOrder); ?>
						</th>

						<th align="left">
							<?php echo JHtml::_('grid.sort', 'COM_BIXMAILING_MASSAMAIL_USERID', 'vmi.company', $listDirn, $listOrder); ?>
						</th>

						<th align="left">
							<?php echo JHtml::_('grid.sort', 'COM_BIXMAILING_MASSAMAIL_TYPE', 'm.type', $listDirn, $listOrder); ?>
						</th>

						<th align="left">
							<?php echo JHtml::_('grid.sort', 'COM_BIXMAILING_MASSAMAIL_STATUS', 'm.status', $listDirn, $listOrder); ?>
						</th>
						<?php if (isset($this->items[0]->state)) { ?>
							<th width="5%">
								<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'm.state', $listDirn, $listOrder); ?>
							</th>
						<?php } ?>
						<?php if (isset($this->items[0]->id)) { ?>
							<th width="1%" class="nowrap">
								<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'm.id', $listDirn, $listOrder); ?>
							</th>
						<?php } ?>
					</tr>
					</thead>
					<tfoot>
					<tr>
						<td colspan="10">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
					</tfoot>
					<tbody>
					<?php
					foreach ($this->items as $i => $item) :
						$canCreate = $user->authorise('core.create', 'com_bixmailing');
						$canEdit = $user->authorise('core.edit', 'com_bixmailing');
						$canCheckin = $user->authorise('core.manage', 'com_bixmailing');
						$canChange = $user->authorise('core.edit.state', 'com_bixmailing');
						?>
						<tr class="row<?php echo $i % 2; ?>">
							<td align="center">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td>
								<?php if (isset($item->checked_out) && $item->checked_out) : ?>
									<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'massas.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_bixmailing&task=massa.edit&id=' . (int)$item->id); ?>">
										<?php echo $item->naam; ?></a>
								<?php else : ?>
									<?php echo $item->naam; ?>
								<?php endif; ?>
							</td>
							<td>
								<?php echo $item->user_company; ?>, <?php echo $item->user_naam; ?>

							</td>
							<td>
								<?php echo JText::_('BIX_MAILING_TYPE_' . strtoupper($item->type)); ?>
							</td>
							<td>
								<?php echo JText::_('BIX_MAILING_STATUS_' . strtoupper($item->status)); ?>
							</td>
							<?php if (isset($this->items[0]->state)) { ?>
								<td class="center">
									<?php echo JHtml::_('jgrid.published', $item->state, $i, 'massas.', $canChange, 'cb'); ?>
								</td>
							<?php } ?>
							<?php if (isset($this->items[0]->id)) { ?>
								<td class="center">
									<?php echo (int)$item->id; ?>
								</td>
							<?php } ?>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<?php echo $this->loadTemplate('batch'); ?>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>