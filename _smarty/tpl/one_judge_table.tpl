{extends file="main.tpl"}
{block name="content"}
<script>
var cols = {$cols};
var rows = [];
var data = { 'cols':cols,'node':'#main-table' };
$(document).ready(function(){
   var table = new Table(data);
})
</script>
<h1>Проверка задач</h1>
<table id="main-table"></table>
{/block}