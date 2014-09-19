<?php
/**
 * File _events_table.php
 *
 * @author Ulashev Roman <truetamtam@gmail.com>
 *
 * @var FintechFab\ActionsCalc\Models\Event[] $events
 */
?>

<table id="events-rules" width="100%">
	<thead>
	<tr>
		<th width="14%">Действия</th>
		<th width="200">sid</th>
		<th>Имя</th>
		<th width="200" class="text-center">Правила</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($events as $event): ?>
		<tr data-id="<?php echo $event->id; ?>">
			<td>
				<ul class="button-group">
					<li>
						<button class="button alert tiny delete-rule"><i class="fi-x"></i></button>
					</li>
					<li>
						<button class="button tiny edit-rule"><i class="fi-page-edit"></i></button>
					</li>
				</ul>
			</td>
			<td class="event-sid"><?php echo $event->event_sid ?></td>
			<td class="event-name"><?php echo $event->name; ?></td>
			<td>
				<ul class="event-buttons button-group right">
					<li>
						<?php $iRulesCount = $event->rules->count(); ?>
						<?php $buttonStatus = $iRulesCount > 0 ? '' : ' disabled'; ?>
						<button data-rules-count="<?php echo $iRulesCount; ?>" class="tiny button see-rules<?php echo $buttonStatus ?>">
							<span><?php echo $event->rules->count(); ?></span>&nbsp;<i class="fi-eye"></i></button>
						<button class="tiny button close-rules" style="display: none;">закрыть</button>
					</li>
					<li>
						<button class="tiny button success rule-create">
							<i class="fi-plus"></i>&nbsp;правило
						</button>
					</li>
				</ul>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<?php if (count($events) < 1): ?>
	<p>Событий не найдено.</p>
<?php endif; ?>

<!-- pagination -->
<?php if (method_exists($events, 'links')): ?>
	<?php echo $events->links(); ?>
	<span id="pagination-events-current-page" class="hide"><?php echo $events->getCurrentPage(); ?></span>
<?php endif; ?>
<!-- /pagination -->