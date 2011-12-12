<?php
//temporary marks
function get_subtasks($task_id) {
    $subtasks = array();
    $res = mysql_query("SELECT subtask_id, max_mark, `comment` FROM subtasks WHERE task_id=$task_id ORDER BY orderby");
    while ($r = mysql_fetch_assoc($res)) {
        $subtasks[] = array(
            'id' => $r['subtask_id'],
            'max' => $r['max_mark'],
            'comment' => $r['comment']
        );
    }
    return $subtasks;
}
function get_temporary_marks($task_id, $judge_id) {
    $marks = array();
    $res = mysql_query("SELECT solution_id, code FROM solutions_tmp WHERE task_id=$task_id AND judge_id=$judge_id");
    while ($r = mysql_fetch_row($res)) {
        $res1 = mysql_query("SELECT mark_id, subtask_id, mark_value FROM marks_tmp WHERE solution_id=$r[0] ORDER BY subtask_id");
        $t = array();
        while ($r1 = mysql_fetch_assoc($res1)) {
            $t[$r1['subtask_id']] = array('id' => $r1['mark_id'], 'value' => $r1['mark_value']);
        }
        $marks[] = array(
            'id'    => $r[0],
            'code'  => $r[1],
            'marks' => $t
        );
    }
    return $marks;
}
?>
