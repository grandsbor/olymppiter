{extends file="main.tpl"}
{block name="content"}
<script>
var cols = {$cols};
var rows = {$rows};
var data = { 'cols':cols,'rows':rows,'node':'#main-table' };
$(document).ready(function(){
   var table = new Table(data);
})
</script>
<h1>Проверка задач ({$judge_name}) [<a href="/compare.php?task_id={$task_id}">сверка</a>] [<a href='login.php?action=logout'>logout</a>]</h1>
<h2></h2>
<input id='judge_id' value="{$judge_id}" type="hidden"><input type="hidden" id='task_id' value="{$task_id}">
<table id="main-table"></table>
{/block}
