<div class="kohana_error">
	<a href="#show_trace" id="kohana_error_trace_link_<?php echo $instance_identifier ?>">
		<?php echo $type ?>
	</a>[ <strong><?php echo $file ?></strong>, line <strong><?php echo $line ?></strong> ]
	<div class="message">
		<p><?php echo $description ?></p>
		<pre><?php echo $message ?></pre>
	</div>
</div>
<div class="kohana_trace" id="kohana_error_trace_<?php echo $instance_identifier ?>">
	<?php if ( ! empty($source)): ?>
		<div class="source">
			<a href="#show_source" id="kohana_error_source_link_<?php echo $instance_identifier ?>">Source</a>
			<pre id="kohana_error_source_<?php echo $instance_identifier ?>"><?php echo $source ?></pre>
		</div>
	<?php endif ?>
	<?php if ( ! empty($trace)): ?>
		<pre class="trace"><?php echo implode("\n", $trace) ?></pre>
	<?php endif ?>
</div>
