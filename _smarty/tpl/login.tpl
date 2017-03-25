{extends file="main.tpl"}
{block name="content"}
<form action="?action=login" method="post">
<select name="judge_id">
{foreach $judges as $judge}<option value="{$judge.id}">{$judge.name}</option>{/foreach}
</select>
Password:
<input type="password" name="passwd"/>
<input type="submit" value="Sign in"/>
</form>
{/block}
