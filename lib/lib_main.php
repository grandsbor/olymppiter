<?php
//common
function strip_zero($n) {
    return $n == (int)$n ? (int)$n : $n;
}
function get_task_list() {
    $out = array();
    $res = mysql_query("SELECT contest_id, contest_name FROM contests WHERE parent_id = 0 ORDER BY contest_id");
    //collect tasks
    while ($r = mysql_fetch_assoc($res)) {
        $contest = array('id' => $r['contest_id'], 'name' => $r['contest_name']);
        $res1 = mysql_query("SELECT task_id, task_name, status FROM tasks WHERE contest_id = ".$r['contest_id']." ORDER BY task_id");
        //tasks may be attached here
        if (mysql_num_rows($res1)) {
            while ($r1 = mysql_fetch_assoc($res1)) {
                $contest['tasks'][] = array(
                    'id' => $r1['task_id'],
                    'name' => $r1['task_name'],
                    'status' => $r1['status'],
                    'judges' => get_judges_for_task($r1['task_id'])
                );
            }
            $out[] = $contest;
            continue;
        }
        //or they may be attached lower
        $res1 = mysql_query("SELECT contest_id, contest_name FROM contests WHERE parent_id = ".$r['contest_id']." ORDER BY contest_id");
        while ($r1 = mysql_fetch_assoc($res1)) {
            $round = array('id' => $r1['contest_id'], 'name' => $r1['contest_name']);
            $res2 = mysql_query("SELECT task_id, task_name FROM tasks WHERE contest_id = ".$r1['contest_id']." ORDER BY task_id");
            while ($r2 = mysql_fetch_assoc($res2)) {
                $round['tasks'][] = array(
                    'id' => $r2['task_id'],
                    'name' => $r2['task_name'],
                    'status' => $r2['status'],
                    'judges' => get_judges_for_task($r2['task_id'])
                );
            }
            $contest['rounds'][] = $round;
        }
        $out[] = $contest;
    }
    return $out;
}
function get_judges_for_task($task_id) {
    //returns array of assoc arrays type (id, name, email)
    $res = mysql_query("SELECT * FROM judges WHERE judge_id IN (SELECT judge_id FROM judges_by_tasks WHERE task_id = $task_id)");
    $out = array();
    while ($r = mysql_fetch_assoc($res)) {
        $out[] = array('id' => $r['judge_id'], 'name' => $r['judge_name'], 'email' => $r['judge_email']);
    }
    return $out;
}
function get_contest_by_task($task_id, $highest = false) {
    $res = mysql_query("SELECT contest_id FROM tasks WHERE task_id = $task_id LIMIT 1");
    if (!mysql_num_rows($res)) return false;
    
    $r = mysql_fetch_row($res);
    if (!$highest) return $r[0];

    $pid = $r[0];
    while ($pid > 0) {
        $r = mysql_fetch_row(mysql_query("SELECT parent_id FROM contests WHERE contest_id = $pid LIMIT 1"));
        if ($r[0] == 0) return $pid;
        $pid = $r[0];
    }
}
//registration forms
//judging
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
            $t[$r1['subtask_id']] = array('id' => $r1['mark_id'], 'value' => strip_zero($r1['mark_value']), 'subtask' => $r1['subtask_id']);
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
    if (!$task_id || !$judge_id) return "Bad task id or judge id";
    //TODO: check if this judge can judge this task
    mysql_query("START TRANSACTION");
    $ret_codes = array();
    foreach($marks as $solution) {
        $code = strtoupper(trim($solution['code']));
        //check the code for validity
        if (!preg_match('/^[0-9]\-[0-9]{2}[A-Z]{3}\-[0-9]$/', $code)) return "Bad code format";
        $code = mysql_real_escape_string($code);
        if ($solution['id']) {
            $solution['id'] = (int)$solution['id'];
            //the code may have changed
            if (!mysql_query("UPDATE solutions_tmp SET code = '$code' WHERE solution_id = ".$solution['id']." LIMIT 1")) return "DB Error";
        }
        else {
            //this is a new solution, let's add it
            //but first check whether the code if unique
            $res = mysql_query("SELECT solution_id FROM solutions_tmp WHERE task_id=$task_id AND judge_id=$judge_id AND code='$code' LIMIT 1");
            if (mysql_num_rows($res)) return "Non-unique code";
            if (!mysql_query("INSERT INTO solutions_tmp VALUES (NULL, $task_id, $judge_id, '$code')")) return "DB Error";
            $solution['id'] = mysql_insert_id();
        }
        $ret_codes[] = $solution['id'];

        foreach($solution['marks'] as $subtask_id => $value) {
            $subtask_id = (int)$subtask_id;
            $value = (float)$value;
            $res = mysql_query("SELECT mark_id FROM marks_tmp WHERE solution_id=".$solution['id']." AND subtask_id=$subtask_id LIMIT 1");
            if (mysql_num_rows($res)) {
                $r = mysql_fetch_row($res);
                if (!mysql_query("UPDATE marks_tmp SET mark_value=$value WHERE mark_id=$r[0] LIMIT 1")) return "DB Error";
            } else {
                if (!mysql_query("INSERT INTO marks_tmp VALUES (NULL, $subtask_id, ".$solution['id'].", $value)")) return "DB Error";
            }
        }
    }
    mysql_query("COMMIT");
    return $ret_codes;
}
function delete_temporary_solution($solution_id) {
    if (!$solution_id) return false;
    if (!mysql_query("DELETE FROM solutions_tmp WHERE solution_id = $solution_id LIMIT 1")) return false;
    return true;
}
function get_aggregate_marks($task_id) {
    $out = array();
    $out_aggr = array();
    $aggr_codes = array();
    //already aggregated
    $res = mysql_query("SELECT solution_id, s.contestant_id, code FROM solutions s LEFT JOIN contestants USING(contestant_id) WHERE task_id = $task_id ORDER BY code");
    while ($r = mysql_fetch_assoc($res)) {
        $out_aggr[$r['code']]['contestant_id'] = (int)$r['contestant_id'];
        $res1 = mysql_query("SELECT subtask_id, mark_value FROM final_marks WHERE solution_id = ".$r['solution_id']." ORDER BY subtask_id");
        while ($r1 = mysql_fetch_assoc($res1)) {
            $out_aggr[$r['code']]['aggregate_marks'][$r1['subtask_id']] = strip_zero($r1['mark_value']);
        }
        $aggr_codes[] = $r['code'];
    }
    //not yet aggregated
    $res = mysql_query("SELECT solution_id, judge_id, t.code, contestant_id FROM solutions_tmp t LEFT JOIN contestants USING (code) WHERE task_id = $task_id ORDER BY t.code");
    while ($r = mysql_fetch_assoc($res)) {

        if (in_array($r['code'], $aggr_codes)) continue;
        
        $out[$r['code']]['invalid'] = 1 - (bool)$r['contestant_id'];
        $out[$r['code']]['contestant_id'] = (int)$r['contestant_id'];

        $res1 = mysql_query("SELECT subtask_id, mark_value FROM marks_tmp WHERE solution_id=".$r['solution_id']." ORDER BY subtask_id");
        
        while ($r1 = mysql_fetch_assoc($res1)) {
            $out[$r['code']]['marks'][$r1['subtask_id']][$r['judge_id']] = strip_zero($r1['mark_value']);
        }
    }
    return array_merge($out, $out_aggr);
}
function save_aggregate_string($task_id, $contestant_id, $code, $marks) {
    if (!$contestant_id) return "No contestant id given";
    if (!$task_id || !is_array($marks) || !$marks) return "No task id given or empty marks";
    
    //it may be an unknown contestant, let's create him then
    mysql_query("START TRANSACTION");
    if ($contestant_id == -1) {
        //but first we have to know contest id
        $contest_id = get_contest_by_task($task_id, true);
        if (!$contest_id || !$code) return false;
        $code = mysql_real_escape_string(strtoupper(trim($code)));
        if (!mysql_query("INSERT INTO contestants VALUES (NULL, 0, $contest_id, '$code')")) return "DB Error";
        $contestant_id = mysql_insert_id();
    }

    //perhaps we need to create a new solution (if it is the first saving)
    $res = mysql_query("SELECT solution_id FROM solutions WHERE task_id = $task_id AND contestant_id = $contestant_id LIMIT 1");
    if (!mysql_num_rows($res)) {
        if (!mysql_query("INSERT INTO solutions VALUES (NULL, $task_id, $contestant_id, '')")) return "DB Error";
        $solution_id = mysql_insert_id();
    } else {
        $r = mysql_fetch_row($res);
        $solution_id = $r[0];
    }

    //save marks (checking whether they exist)
    $old_marks = array();
    $res = mysql_query("SELECT mark_id, subtask_id FROM final_marks WHERE solution_id = $solution_id");
    while ($r = mysql_fetch_assoc($res)) {
        $old_marks[$r['subtask_id']] = $r['mark_id'];
    }
    foreach ($marks as $sid => $val) {
        if (isset($old_marks[$sid])) {
            //update
            if (!mysql_query("UPDATE final_marks SET mark_value = ".(float)$val." WHERE mark_id = ".$old_marks[$sid]." LIMIT 1")) return "DB Error";
        } else {
            //insert
            if (!mysql_query("INSERT INTO final_marks VALUES (NULL, $sid, $solution_id, ".(float)$val.")")) return "DB Error";
        }
    }
    mysql_query("COMMIT");
    return $contestant_id;
}
?>
