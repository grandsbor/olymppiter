<?php
//common
function strip_zero($n) {
    return $n == (int)$n ? (int)$n : $n;
}
function fetch($query) {
    global $pdo_db;
    return $pdo_db->query($query)->fetchAll();
}
function get_task_list() {
    $out = array();
    $res = fetch("SELECT contest_id, contest_name FROM contests WHERE parent_id = 0 ORDER BY contest_id");
    //collect tasks
    foreach ($res as $r) {
        $contest = array('id' => $r['contest_id'], 'name' => $r['contest_name']);
        $res1 = fetch("SELECT task_id, task_name, status FROM tasks WHERE contest_id = ".$r['contest_id']." ORDER BY task_id");
        //tasks may be attached here
        if (count($res1) > 0) {
            foreach ($res1 as $r1) {
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
        $res1 = fetch("SELECT contest_id, contest_name FROM contests WHERE parent_id = ".$r['contest_id']." ORDER BY contest_id");
        foreach ($res1 as $r1) {
            $round = array('id' => $r1['contest_id'], 'name' => $r1['contest_name']);
            $res2 = fetch("SELECT task_id, task_name FROM tasks WHERE contest_id = ".$r1['contest_id']." ORDER BY task_id");
            foreach ($res2 as $r2) {
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
    $res = fetch("SELECT * FROM judges WHERE judge_id IN (SELECT judge_id FROM judges_by_tasks WHERE task_id = $task_id)");
    $out = array();
    foreach ($res as $r) {
        $out[] = array('id' => $r['judge_id'], 'name' => $r['judge_name'], 'email' => $r['judge_email']);
    }
    return $out;
}
function get_contest_by_task($task_id, $highest = false) {
    global $pdo_db;
    $res = fetch("SELECT contest_id FROM tasks WHERE task_id = $task_id LIMIT 1");
    if (!count($res)) return false;
    
    $r = $res[0];
    if (!$highest)
        return $r['contest_id'];

    $pid = $r['contest_id'];
    while ($pid > 0) {
        $tr = fetch("SELECT parent_id FROM contests WHERE contest_id = $pid LIMIT 1");
        $r = $tr[0];
        if ($r['contest_id'] == 0) return $pid;
        $pid = $r['contest_id'];
    }
}
//registration forms
function check_judge_passwd($id, $passwd) {
    global $pdo_db;
    $hash = md5(trim($passwd));
    $res = $pdo_db->query("SELECT * FROM judges WHERE judge_id=$id AND pwd_hash='$hash' LIMIT 1");
    return $res->rowCount() > 0;
}
//judging
function get_all_judges() {
    global $pdo_db;
    $res = $pdo_db->query("SELECT judge_id, judge_name FROM judges ORDER BY judge_name");
    $out = array();
    foreach ($res as $r)
        $out[] = array('id' => $r['judge_id'], 'name' => $r['judge_name']);
    return $out;
}
function get_judge_name($id) {
    global $pdo_db;
    $res = $pdo_db->query("SELECT judge_name FROM judges WHERE judge_id = $id LIMIT 1");
    return $res->fetchAll()[0]["judge_name"];
}
function get_tasks_for_judge($id) {
    global $pdo_db;
    $res = $pdo_db->query("
        SELECT task_id, task_name, c.contest_name
        FROM judges_by_tasks jt
        LEFT JOIN tasks t
            USING (task_id)
        LEFT JOIN contests c
            USING (contest_id)
        WHERE judge_id = $id
        ORDER BY 1
    ");
    $out = array();
    foreach ($res as $r)
        $out[] = array('id' => $r['task_id'], 'title' => $r['task_name'], 'contest' => $r['contest_name']);
    return $out;
}
function get_subtasks($task_id) {
    global $pdo_db;
    $subtasks = array();
    $res = $pdo_db->query("SELECT subtask_id, max_mark, `comment` FROM subtasks WHERE task_id=$task_id ORDER BY orderby");
    foreach ($res as $r) {
        $subtasks[] = array(
            'id' => $r['subtask_id'],
            'max' => $r['max_mark'],
            'name' => $r['comment']
        );
    }
    return $subtasks;
}
function get_temporary_marks($task_id, $judge_id) {
    global $pdo_db;
    $marks = array();
    $res = fetch("SELECT solution_id, code FROM solutions_tmp WHERE task_id=$task_id AND judge_id=$judge_id");
    foreach ($res as $r) {
        $res1 = fetch("SELECT mark_id, subtask_id, mark_value FROM marks_tmp WHERE solution_id=".$r['solution_id']." ORDER BY subtask_id");
        $t = array();
        foreach ($res1 as $r1) {
            $t[$r1['subtask_id']] = array('id' => $r1['mark_id'], 'value' => strip_zero($r1['mark_value']), 'subtask' => $r1['subtask_id']);
        }
        $marks[] = array(
            'id'    => $r['solution_id'],
            'code'  => $r['code'],
            'fields' => $t
        );
    }
    return $marks;
}
function save_temporary_marks($task_id, $judge_id, $marks) {
    global $pdo_db;
    // $marks is an array type (code, array of arrays type (subtask_id => value))
    if (!$task_id || !$judge_id) return "Bad task id or judge id";
    //TODO: check if this judge can judge this task
    $pdo_db->beginTransaction();
    $ret_codes = array();
    foreach ($marks as $solution) {
        $code = strtoupper(trim($solution['code']));
        //check the code for validity
        if (!preg_match('/^[0-9]\-[0-9]{2}[A-Z]{3}\-[0-9]$/', $code)) return "Bad code format";
        if ($solution['id']) {
            $solution['id'] = (int)$solution['id'];
            //the code may have changed
            $pdo_db->query("UPDATE solutions_tmp SET code = '$code' WHERE solution_id = ".$solution['id']." LIMIT 1");
        }
        else {
            //this is a new solution, let's add it
            //but first check whether the code if unique
            $res = $pdo_db->query("SELECT solution_id FROM solutions_tmp WHERE task_id=$task_id AND judge_id=$judge_id AND code='$code' LIMIT 1");
            if ($res->rowCount() > 0) return "Non-unique code";
            $pdo_db->query("INSERT INTO solutions_tmp VALUES (NULL, $task_id, $judge_id, '$code')");
            $solution['id'] = $pdo_db->lastInsertId();
        }
        $ret_codes[] = $solution['id'];

        foreach ($solution['marks'] as $subtask_id => $value) {
            $subtask_id = (int)$subtask_id;
            $value = (float)$value;
            $res = fetch("SELECT mark_id FROM marks_tmp WHERE solution_id=".$solution['id']." AND subtask_id=$subtask_id LIMIT 1");
            if (count($res) > 0) {
                $r = $res[0];
                $pdo_db->query("UPDATE marks_tmp SET mark_value=$value WHERE mark_id=".$r['mark_id']." LIMIT 1");
            } else {
                $pdo_db->query("INSERT INTO marks_tmp VALUES (NULL, $subtask_id, ".$solution['id'].", $value)");
            }
        }
    }
    $pdo_db->commit();
    return $ret_codes;
}
function delete_temporary_solution($solution_id) {
    global $pdo_db;
    if (!$solution_id) return false;
    $pdo_db->query("DELETE FROM solutions_tmp WHERE solution_id = $solution_id LIMIT 1");
    return true;
}
function get_aggregate_marks($task_id) {
    global $pdo_db;
    $out = array();
    $out_aggr = array();
    $aggr_codes = array();
    //already aggregated
    $res = fetch("SELECT solution_id, s.contestant_id, code FROM solutions s LEFT JOIN contestants USING(contestant_id) WHERE task_id = $task_id ORDER BY code");
    foreach ($res as $r) {
        $out_aggr[$r['code']]['contestant_id'] = (int)$r['contestant_id'];
        $res1 = $pdo_db->query("SELECT subtask_id, mark_value FROM final_marks WHERE solution_id = ".$r['solution_id']." ORDER BY subtask_id");
        foreach ($res1 as $r1) {
            $out_aggr[$r['code']]['aggregate_marks'][$r1['subtask_id']] = strip_zero($r1['mark_value']);
        }
        $aggr_codes[] = $r['code'];
    }
    //not yet aggregated
    $res = fetch("
        SELECT solution_id, judge_id, t.code, contestant_id
        FROM solutions_tmp t
        LEFT JOIN contestants USING (code)
        LEFT JOIN tasks USING (task_id)
        WHERE task_id = $task_id
            AND (
                contestants.contest_id=tasks.contest_id
                OR contestants.contestant_id IS NULL
            )
        ORDER BY t.code
    ");
    foreach ($res as $r) {

        if (in_array($r['code'], $aggr_codes)) continue;
        
        $out[$r['code']]['invalid'] = 1 - (bool)$r['contestant_id'];
        $out[$r['code']]['contestant_id'] = (int)$r['contestant_id'];

        $res1 = $pdo_db->query("SELECT subtask_id, mark_value FROM marks_tmp WHERE solution_id=".$r['solution_id']." ORDER BY subtask_id");
        
        foreach ($res1 as $r1) {
            $out[$r['code']]['marks'][$r1['subtask_id']][$r['judge_id']] = strip_zero($r1['mark_value']);
        }
    }
    return array_merge($out, $out_aggr);
}
function save_aggregate_string($task_id, $contestant_id, $code, $marks) {
    global $pdo_db;
    if (!$contestant_id) return "No contestant id given";
    if (!$task_id || !is_array($marks) || !$marks) return "No task id given or empty marks";
    
    //it may be an unknown contestant, let's create him then
    $pdo_db->beginTransaction();
    if ($contestant_id == -1) {
        //but first we have to know contest id
        $contest_id = get_contest_by_task($task_id, true);
        if (!$contest_id || !$code) return false;
        $code = $pdo_db->quote(strtoupper(trim($code)));
        $pdo_db->query("INSERT INTO contestants VALUES (NULL, 0, $contest_id, '$code')");
        $contestant_id = $pdo_db->lastInsertId();
    }

    //perhaps we need to create a new solution (if it is the first saving)
    $res = fetch("SELECT solution_id FROM solutions WHERE task_id = $task_id AND contestant_id = $contestant_id LIMIT 1");
    if (count($res) == 0) {
        $pdo_db->query("INSERT INTO solutions VALUES (NULL, $task_id, $contestant_id, '')");
        $solution_id = $pdo_db->lastInsertId();
    } else {
        $r = $res[0];
        $solution_id = $r['solution_id'];
    }

    //save marks (checking whether they exist)
    $old_marks = array();
    $res = fetch("SELECT mark_id, subtask_id FROM final_marks WHERE solution_id = $solution_id");
    foreach ($res as $r) {
        $old_marks[$r['subtask_id']] = $r['mark_id'];
    }
    foreach ($marks as $sid => $val) {
        if (isset($old_marks[$sid])) {
            //update
            $pdo_db->query("UPDATE final_marks SET mark_value = ".(float)$val." WHERE mark_id = ".$old_marks[$sid]." LIMIT 1");
        } else {
            //insert
            $pdo_db->query("INSERT INTO final_marks VALUES (NULL, $sid, $solution_id, ".(float)$val.")");
        }
    }
    $pdo_db->commit();
    return $contestant_id;
}
function get_task_result($task_id) {
    global $pdo_db;
    $contest_id = get_contest_by_task($task_id);
    
    $res = fetch("select contestant_id, ceil(sum(mark_value)) as mark from solutions left join final_marks using(solution_id) where task_id = $task_id group by solution_id");
    $solutions = array();
    foreach ($res as $r) {
        $solutions[$r['contestant_id']] = $r['mark'];
    }
    //var_dump($solutions);
    $res1 = $pdo_db->query("select code, contestant_id from contestants where contest_id = " . $contest_id . " order by code");
    $contestants = array();
    foreach ($res1 as $r1) {
        $contestants[$r1['code']] = isset($solutions[$r1['contestant_id']]) ? $solutions[$r1['contestant_id']] : null;
    }
    return $contestants;
}
?>
