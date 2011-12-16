{extends file="main.tpl"}
{block name="content"}
<script>
   var judges = {$judges|json_encode};
   $(document).ready(function(){
      $('#main-table').delegate('.marktable-td-submit a','click',function(event){
         var $a = $(event.target).closest('a');
         event.preventDefault();
         var $tr = $a.closest('tr');
         var has_errors = false;
         var data = { };
         $tr.find('input.marktable-mark').each(function(){
            var $input = $(this);
            if(parseFloat($input.val()) != $input.val()) {
               has_errors = true;
               $input.addClass('error');
            }
            else {
               data[$input.attr('name')] = $input.val();
               $input.removeClass('error');
            }
         })
         if(has_errors) {
            $tr.find('.marktable-td-error').text('Нельзя сохранять.');
            $tr.find('.marktable-td-sum').addClass('error');
         }
         else {
            $tr.find('.marktable-td-error').text('');
            $tr.find('.marktable-td-sum').removeClass('error');
            data.code = $tr.data('code');
            data.id = $tr.data('id');
            data.action = 'save_aggr';
            data.task_id = {$task_id};
            $tr.find('input.marktable-mark').attr('disabled','disabled');
            $.getJSON('ajax.php',data,function(res) {
               if(res.result == true) {
                  $tr.find('input.marktable-mark').removeAttr('disabled');
                  $tr.addClass('saved');
               }
               else if(res.message) {
                  $tr.find('.marktable-td-error').text(message);
               }
               else {
                  $tr.find('.marktable-td-error').text('Не удалось сохранить.');
               }
            });
         }
      })
      $('#main-table').delegate('input.marktable-mark','blur',function(event){
         var $input = $(event.target);
         var $tr = $input.closest('tr');
         recount($tr);
      })
   })
   function recount($tr) {
      var sum = [];
      var i = 0;
      for (i=0; i<judges.length; ++i) {
          sum[i] = 0;
      }
      $tr.find('input.marktable-mark').each(function(){
         var $input = $(this);
         if(parseFloat($input.val()) != $input.val()) {
            $input.addClass('error');
            var values = $input.val().split('/');
            for(i=0;i<values.length;++i) {
               if(typeof(sum[i]) == 'undefined') {
                  sum[i] = parseFloat(trim(values[i]));
               }
               else {
                  sum[i] += parseFloat(trim(values[i]));
               }
            }
         }
         else {
            $input.removeClass('error');
            for(i=0;i<(sum.length ? sum.length : 1);++i){
               if(typeof(sum[i]) == 'undefined') {
                  sum[i] = parseFloat(trim($input.val()));
               }
               else {
                  sum[i] += parseFloat(trim($input.val()));
               }
            }
         }
      })
      var compare_sum = null;
      var sum_str = '';
      for(i=0;i<sum.length;++i){
         if(compare_sum == null) {
            compare_sum = sum[i];
            sum_str += sum[i].toString();
         }
         else if(compare_sum != sum[i]) {
            compare_sum = sum[i];
            sum_str += ' / ' + sum[i];
         }
      }
      $tr.find('.marktable-td-sum').text(sum_str);
   }
</script>
<h1>Сверка</h1>
<a href="result.php?task_id={$task_id}">Сохранить файл</a>
<input type="hidden" id='task_id' value="{$task_id}">
<table id="main-table" class="marktable">
<thead>
	<tr>
		<th>Код</th>{foreach $cols as $col}<th>{$col.name} (max {$col.max})</th>{/foreach}<th>Итого</th>
	</tr>
</thead>
{foreach $data as $code=>$row}
	{$row.differ = false}
	{$row.missing_marks = false}
	{$row.sum = array()}
	<tr data-id="{$row.contestant_id}" data-code="{$code}" class="{if $row.aggregate_marks}saved{/if}">
		<td class="marktable-td-code">{$code}</td>
      {if isset($row.marks)}
         {foreach $row.marks as $subtask_id => $subtask}
            {$compare_value = null}
            {$value_string = ''}
            {if sizeof($subtask) < sizeof($judges)}{$row.missing_marks = true}{/if}
            {foreach $subtask as $judge_id => $value}
               {$mark_differ = false}
               {if isset($row.sum[$judge_id])}
                  {$row.sum[$judge_id] = $row.sum[$judge_id] + $value}
               {else}
                  {$row.sum[$judge_id] = $value}
               {/if}
               {if $compare_value === null}
                  {$compare_value = $value}
                  {$value_string = $value}
               {elseif $value != $compare_value}
                  {$compare_value = $value}
                  {$mark_differ = true}
                  {$row.differ = true}
                  {$value_string = "$value_string / $value"}
               {/if}
            {/foreach}
            <td class="marktable-td-mark"><input type="text" name="marks[{$subtask_id}]" value="{$value_string}" class="marktable-mark{if $mark_differ} error{/if}"></td>
         {/foreach}
         <td class="marktable-td-sum{if $row.differ} error{/if}">
         {$compare_sum = null}
         {foreach $row.sum as $judge_id => $sum}
            {if $compare_sum === null}
               {$compare_sum = $sum}
               {$sum}
            {elseif $sum != $compare_sum}
               / {$sum}
            {/if}
         {/foreach}
      </td>
      {elseif $row.aggregate_marks}
         {$sum = 0}
         {foreach $row.aggregate_marks as $subtask_id => $value}
            {$sum = $sum + $value}
            <td class="marktable-td-mark"><input type="text" name="marks[{$subtask_id}]" value="{$value}" class="marktable-mark"></td>
         {/foreach}
         <td class="marktable-td-sum">{$sum}</td>
      {/if}
      <td class="marktable-td-submit"><a href="#">Сохранить</a></td>
		<td class="marktable-td-error">
			{if $row.differ}Есть различия. {/if}
			{if $row.missing_marks}Не хватает оценок одного из судей.{/if}
			{if isset($row.invalid) && $row.invalid}Кода нет в базе.{/if}
		</td>
	</tr>
{/foreach}
</table>
{/block}
