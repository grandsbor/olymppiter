{extends file="main.tpl"}
{block name="content"}
<h1>Сверка</h1>
<input type="hidden" id='task_id' value="{$task_id}">
<table id="main-table">
<thead>
	<tr>
		<th>Код</th>{foreach $cols as $col}<th>{$col.name} (max $col.max)</th>{/foreach}<th>Итого</th>
	</tr>
</thead>
{foreach $data as $code=>$row}
	{$row.differ = false}
	{$row.missing_marks = false}
	{$row.sum = array()}
	<tr>
		<td>{$code}</td>
		{foreach $row.marks as $subtask_id => $subtask}
			{$compare_value = null}
			{$value_string = ''}
			{if sizeof($subtask) < sizeof($judges)}{$row.missing_marks = true}{/if}
			{foreach $subtask as $judge_id => $value}
				{if isset($row.sum[$judge_id])}
					{$row.sum[$judge_id] = $row.sum[$judge_id] + $value}
				{else}
					{$row.sum[$judge_id] = $value}
				{/if}
				{if $compare_value == null}
					{$compare_value = $value}
					{$value_string = $value}
				{elseif $value != $compare_value}
					{$compare_value = $value}
					{$row.differ = true}
					{$value_string = "$value_string / $value"}
				{/if}
			{/foreach}
			<td>{$value_string}</td>
		{/foreach}
		<td></td>
		<td>
			{if $row.differ}Есть различия. {/if}
			{if $row.missing_marks}Не хватает оценок одного из судей.{/if}
			{if $row.invalid}Кода нет в базе.{/if}
		</td>
	</tr>
{/foreach}
</table>
{/block}