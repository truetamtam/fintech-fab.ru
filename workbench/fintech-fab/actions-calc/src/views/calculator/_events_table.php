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
		<th width="200">sid</th>
		<th>Имя</th>
		<th width="200" class="text-center">Правила</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($events as $event): ?>
		<tr data-id="<?php echo $event->id; ?>">
			<td><?php echo $event->event_sid ?></td>
			<td><?php echo $event->name; ?></td>
			<td>
				<ul class="event-buttons button-group right">
					<li>
						<a data-rules-count="<?php echo $event->rules->count(); ?>" href="#" class="tiny button see-rules">
							<?php echo $event->rules->count(); ?>&nbsp;<i class="fi-eye"></i> </a>
						<a href="#" class="tiny button close-rules" style="display: none;">закрыть</a>
					</li>
					<li><a href="#" class="tiny button success"><i class="fi-plus"></i></a></li>
				</ul>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<!-- pagination -->
<?php if (method_exists($events, 'links')): ?>
	<?php echo $events->links(); ?>
<?php endif; ?>
<!-- /pagination -->