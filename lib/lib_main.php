<?php
//registration forms
//temporary marks
function get_subtasks($task_id) {
    $subtasks = array();
    $res = mysql_query("SELECT subtask_id, max_mark, `comment` FROM subtasks WHERE task_id=$task_id ORDER BY orderby");
    while ($r = mysql_fetch_assoc($res)) {
        $subtasks[] = array(
            'id' => $r['subtask_id'],
            'max' => $r['max_mark'],
            'name' => $r['comment']
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
            $t[$r1['subtask_id']] = array('id' => $r1['mark_id'], 'value' => $r1['mark_value'], 'subtask' => $r1['subtask_id']);
        }
        $marks[] = array(
            'id'    => $r[0],
            'code'  => $r[1],
            'fields' => $t
        );
    }
    return $marks;
}
function save_temporary_marks($task_id, $judge_id, $marks) {
    // $marks is an array type (code, array of arrays type (subtask_id => value))
    if (!$task_id || !$judge_id) return false;
    //TODO: check if this judge can judge this task
    mysql_query("START TRANSACTION");
    $ret_codes = array();
    foreach($marks as $solution) {
        $code = mysql_real_escape_string(strtoupper($solution['code']));
        if ($solution['id']) {
            $solution['id'] = (int)$solution['id'];
            //the code may have changed
            if (!mysql_query("UPDATE solutions_tmp SET code = '$code' WHERE solution_id = ".$solution['id']." LIMIT 1")) return false;
        }
        else {
            //this is a new solution, let's add it
            if (!mysql_query("INSERT INTO solutions_tmp VALUES (NULL, $task_id, $judge_id, '$code')")) return false;
            $solution['id'] = mysql_insert_id();
        }
        $ret_codes[] = $solution['id'];

        foreach($solution['marks'] as $subtask_id => $value) {
            $subtask_id = (int)$subtask_id;
            $value = (int)$value;
            $res = mysql_query("SELECT mark_id FROM marks_tmp WHERE solution_id=".$solution['id']." AND subtask_id=$subtask_id LIMIT 1");
            if (mysql_num_rows($res)) {
                $r = mysql_fetch_row($res);
                if (!mysql_query("UPDATE marks_tmp SET mark_value=$value WHERE mark_id=$r[0] LIMIT 1")) return false;
            } else {
                if (!mysql_query("INSERT INTO marks_tmp VALUES (NULL, $subtask_id, ".$solution['id'].", $value)")) return false;
            }
        }
    }
    mysql_query("COMMIT");
    return $ret_codes;
}
function get_aggregate_marks($task_id) {
    $out = array();
    $res = mysql_query("SELECT solution_id, judge_id, t.code, contestant_id LEFT JOIN contestants USING code FROM solutions_tmp t WHERE task_id = $task_id ORDER BY t.code");
    while ($r = mysql_fetch_assoc($res)) {
        $out[$r['code']]['invalid'] = 1 - (bool)$r['contestant_id'];
        $res1 = mysql_query("SELECT mark_id, subtask_id, mark_value FROM marks_tmp WHERE solution_id=".$r['solution_id']." ORDER BY subtask_id");
        $t = array();
        while ($r1 = mysql_fetch_assoc($res1)) {
            $t[] = array(
                'id' => $r1['mark_id'],
                'subtask_id' => $r1['subtask_id'],
                'value' => $r1['mark_value']
            );
        }
        $out[$r['code']]['judgments'][] = array(
            'id' => $r['solution_id'],
            'judge_id' => $r['judge_id'],
            'marks' => $t
        );
    }
    return $out;
}
?>
