<?php
class sql
{
    public static $sql;

    function __construct()
    {
        global $_config;
        self::数据库连接($_config['数据库']['地址'], $_config['数据库']['用户名'], $_config['数据库']['密码'], $_config['数据库']['名称'], $_config['数据库']['端口']);
    }

    private static function 数据库连接($hostname, $username, $password, $db, $port)
    {
        $sql = new mysqli($hostname, $username, $password, $db, $port);;
        if ($sql->connect_error) {
            showmessage(-1, '数据库错误:' . $sql->connect_errno . "\n" . $sql->connect_error);
        }
        self::$sql = $sql;
    }

    public static function 插入_弹幕($data)
    {
        try {
            $stmt = self::$sql->prepare("INSERT IGNORE INTO sea_danmaku_list (id, type, text, color, size, videotime, ip, time, user) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            @$stmt->bind_param('sssssssss', $data['id'], $data['type'], $data['text'], $data['color'], $data['size'], $data['time'], $_SERVER['REMOTE_ADDR'], time(), $data['author']);
            if ($stmt->execute() == false) {
                throw new Exception($stmt->error_list);
            }
            $stmt->close();
        } catch (Exception $e) {
            showmessage(-1, $e->getMessage());
        }
    }

    public static function 插入_发送弹幕次数($ip)
    {
        try {
            $stmt = self::$sql->prepare("INSERT IGNORE INTO sea_danmaku_ip (ip, time) VALUES (?, ?)");
            @$stmt->bind_param('si', $ip, time());
            if ($stmt->execute() == false) {
                throw new Exception($stmt->error_list);
            }
            $stmt->close();
        } catch (Exception $e) {
            showmessage(-1, $e->getMessage());
        }
    }

    public static function 查询_弹幕池($id)
    {
        try {
            $stmt = self::$sql->prepare("SELECT * FROM sea_danmaku_list WHERE id=?");
            $stmt->bind_param('s', $id);
            if ($stmt->execute() == false) {
                throw new Exception($stmt->error_list);
            }
            $data = self::fetchAll($stmt->get_result());
            $stmt->close();
            return $data;
        } catch (Exception $e) {
            showmessage(-1, $e->getMessage());
        }
    }

    public static function 查询_发送弹幕次数($ip)
    {
        try {
            $stmt = self::$sql->prepare("SELECT * FROM sea_danmaku_ip WHERE ip = ? LIMIT 1");
            $stmt->bind_param('s', $ip);
            if ($stmt->execute() == false) {
                throw new Exception($stmt->error_list);
            }
            $data = self::fetchAll($stmt->get_result());
            $stmt->close();
            return $data;
        } catch (Exception $e) {
            showmessage(-1, $e->getMessage());
        }
    }
	
	public static function 搜索_弹幕池($key)
    {
        try {
            $stmt = self::$sql->prepare("SELECT * FROM sea_danmaku_list WHERE text like '%$key%' or id like '%$key%' ORDER BY time DESC");
         
            if ($stmt->execute() == false) {
                throw new Exception($stmt->error_list);
            }
            $data = self::fetchAll($stmt->get_result());
            return $data;
        } catch (PDOException $e) {
            showmessage(-1, '数据库错误:' . $e->getMessage());
        }
    }

    public static function 更新_发送弹幕次数($ip, $time = 'time')
    {
        try {
            $query = "UPDATE sea_danmaku_ip SET c=c+1,time=$time WHERE ip = ?";
            if (is_int($time)) $query = "UPDATE sea_danmaku_ip SET c=1,time=$time WHERE ip = ?";
            $stmt = self::$sql->prepare($query);
            $stmt->bind_param('s', $ip);
            if ($stmt->execute() == false) {
                throw new Exception($stmt->error_list);
            }
            $stmt->close();
        } catch (Exception $e) {
            showmessage(-1, $e->getMessage());
        }
    }
	
	public static function 举报_弹幕($ip)
    {
		try {
            $stmt = self::$sql->prepare("INSERT IGNORE INTO sea_danmaku_report (id,cid,text,type,time,ip) VALUES (?,?,?,?,?,?)");
            @$stmt->bind_param('ssssss', $_GET['title'],$_GET['cid'],$_GET['text'],$_GET['type'],time(),$_SERVER['REMOTE_ADDR']);
            if ($stmt->execute() == false) {
                throw new Exception($stmt->error_list);
            }
            $stmt->close();
        } catch (Exception $e) {
            showmessage(-1, $e->getMessage());
        }
	}
	public static function 显示_弹幕列表()
    {
        try {
            global $_config;
            $page = 1;
            if (isset($_GET['page'])) {
                $page = $_GET['page'];
            }
            $limit = $_GET['limit'];
			$conn = @new mysqli($_config['数据库']['地址'], $_config['数据库']['用户名'], $_config['数据库']['密码'], $_config['数据库']['名称'], $_config['数据库']['端口']);
            $conn->set_charset('utf8');
            $sql = "select count(*) from sea_danmaku_list ORDER BY time DESC";
            $res = $conn->query($sql);
            $length = $res->fetch_row();
            $count = $length[0];
            $index = ($page - 1) * $limit;	
            $stmt = self::$sql->prepare("SELECT * FROM sea_danmaku_list ORDER BY time DESC limit $index,$limit"); 		
            if($stmt->execute() == false) {
                throw new Exception($stmt->error_list);
            }
            $data = self::fetchAll($stmt->get_result());
            $stmt->close();
            return $data;

        } catch (PDOException $e) {
            showmessage(-1, '数据库错误:' . $e->getMessage());
        }
    }
    public static function 显示_举报列表()
    {
        try {
            global $_config;
            $page = 1;
            if (isset($_GET['page'])) {
                $page = $_GET['page'];
            }
            $limit = $_GET['limit'];
			$conn = @new mysqli($_config['数据库']['地址'], $_config['数据库']['用户名'], $_config['数据库']['密码'], $_config['数据库']['名称'], $_config['数据库']['端口']);
            $conn->set_charset('utf8');
            $sql = "select count(*) from sea_danmaku_report ORDER BY time DESC";
            $res = $conn->query($sql);
            $length = $res->fetch_row();
            $count = $length[0];
            $index = ($page - 1) * $limit;	
            $stmt = self::$sql->prepare("SELECT * FROM sea_danmaku_report ORDER BY time DESC limit $index,$limit"); 		
            if($stmt->execute() == false) {
                throw new Exception($stmt->error_list);
            }
            $data = self::fetchAll($stmt->get_result());
            $stmt->close();
            return $data;

        } catch (PDOException $e) {
            showmessage(-1, '数据库错误:' . $e->getMessage());
        }
    }
   public static function 删除_弹幕数据($id)
    {
        try {
            global $_config;
            $conn = @new mysqli($_config['数据库']['地址'], $_config['数据库']['用户名'], $_config['数据库']['密码'], $_config['数据库']['名称'], $_config['数据库']['端口']);
            $conn->set_charset('utf8');
            if ($_GET['type'] == "list") {
                $sql = "DELETE FROM sea_danmaku_report WHERE cid={$id}";
                $result = "DELETE FROM sea_danmaku_list WHERE cid={$id}";
                $conn->query($sql);
                $conn->query($result);
            } else if ($_GET['type'] == "report") {
                $sql = "DELETE FROM sea_danmaku_report WHERE cid={$id}";
                $conn->query($sql);
            }
        } catch (PDOException $e) {
            showmessage(-1, '数据库错误:' . $e->getMessage());
        }
    }
    public static function 编辑_弹幕($cid)
    {
        try {
            global $_config;
            $text = $_POST['text'];
            $color = $_POST['color'];
            $conn = @new mysqli($_config['数据库']['地址'], $_config['数据库']['用户名'], $_config['数据库']['密码'], $_config['数据库']['名称'], $_config['数据库']['端口']);
            
            $sql = "UPDATE sea_danmaku_list SET text='$text',color='$color' WHERE cid=$cid";
            $result = "UPDATE sea_danmaku_report SET text='$text',color='$color' WHERE cid=$cid";
            $conn->query($sql);
            $conn->query($result);
        } catch (PDOException $e) {
            showmessage(-1, '数据库错误:' . $e->getMessage());
        }
    }
   private static function fetchAll($obj)
    {
        $data = [];
        if ($obj->num_rows > 0) {
            while ($arr = $obj->fetch_assoc()) {
                $data[] = $arr;
            }
        }
        $obj->free();
        return $data;
    }
}
