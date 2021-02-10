<?php
/**
 * author FengMo
 * link http://Mo66.cn
 */
class BilibiliAnimeInfo{
    public $title=array();//标题
    public $image_url=array();//图片链接
    public $total=array();//总集数
    public $progress=array();//我的进度
    public $evaluate=array();//介绍
    public $season_id=array();//ID号，用于番剧跳转
    public $sum;//番剧数目
    private $cookie;//b站cookie
//    这个是处理我观看记录的函数
    private function process($content)
    {
        $start=stripos($content,"第");
        if($start)
        {
            $end=stripos($content,"话");
            return substr($content,$start+3,$end-$start-3);
        }
        else
        {
            $start=stripos($content,"到");
            if($start)
            {
                return substr($content,$start+3);
            }
            else
            {
                $start=stripos($content,"完");
                if($start)
                {
                    return substr($content,$start+3);
                }
                else
                {
                    return "没有记录!";
                }
            }
        }
    }
    private function getpage($uid)
    {
        $url="https://api.bilibili.com/x/space/bangumi/follow/list?type=1&follow_status=0&pn=1&ps=15&vmid=$uid";
        $ch = curl_init(); //初始化curl模块
        curl_setopt($ch, CURLOPT_URL, $url); //登录提交的地址
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);//这个很关键就是把获取到的数据以文件流的方式返回，而不是直接输出
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            //发送请求头
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36",
            "Referer: https://www.bilibili.com/",

        ));

        $info=json_decode(curl_exec($ch),true);
        curl_close($ch);//关闭连接
        return $info['data']['total'];
    }
    public function __construct($uid,$cookie)
    {
        if(!file_exists(__DIR__.'/json/data.json') or time()-filectime(__DIR__.'/json/data.json') >= 86400){//文件不存在或文件已过期
            # 文件不存在或已过期
            if(!is_dir(__DIR__.'/json/')){
                # 判断文件夹是否存在
                mkdir(__DIR__.'/json/');
            }
            $this->sum=$this->getpage($uid,$cookie);
            for($i=1;$i<=ceil($this->sum/15);$i++)
            {
                $url="https://api.bilibili.com/x/space/bangumi/follow/list?type=1&follow_status=0&pn=$i&ps=15&vmid=$uid";
                $ch = curl_init(); //初始化curl模块
                curl_setopt($ch, CURLOPT_URL, $url); //登录提交的地址
                curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);//这个很关键就是把获取到的数据以文件流的方式返回，而不是直接输出
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    //发送请求头
                    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36",
                    "Referer: https://www.bilibili.com/",
                    "Cookie: $cookie",
                ));
                $info=json_decode(curl_exec($ch),true);
                curl_close($ch);//关闭连接
                foreach ($info['data']['list'] as $data) {
                    if(!empty($data['cover'])){
                        $data['cover']=str_replace('http://','https://',$data['cover']);
                    }
                    array_push($this->title, $data['title']);
                    array_push($this->image_url, $data['cover']);
                    array_push($this->total, $data['new_ep']['title']);
                    array_push($this->progress,$this->process($data['progress']));
                    array_push($this->evaluate, $data['evaluate']);
                    array_push($this->season_id, $data['season_id']);
                }
            }
            $file_json_str = array(
                'title'=>$this->title,
                'image_url'=>$this->image_url,
                'total'=>$this->total,
                'progress'=>$this->progress,
                'evaluate'=>$this->evaluate,
                'season_id'=>$this->season_id,
                'sum'=>$this->sum,
            );//用来保存要写入文件的数据

            $file_json_str['progress'] = mb_convert_encoding($file_json_str['progress'], "utf-8");
            // var_dump(json_encode($file_json_str,JSON_UNESCAPED_UNICODE));
            $file_json_str = json_encode($file_json_str,JSON_UNESCAPED_UNICODE);


            $f=fopen(__DIR__.'/json/data.json','w');//打开文件
            fwrite($f,$file_json_str);//写入
            fclose($f);//关闭文件
        }else{
            # 文件可用，直接读即可
            $f = fopen(__DIR__.'/json/data.json','r');

            $info=json_decode(fread($f,filesize(__DIR__.'/json/data.json')),true);
            // var_dump($info);
            $this->title=$info['title'];
            $this->image_url=$info['image_url'];
            $this->total=$info['total'];
            $this->progress=$info['progress'];
            $this->evaluate=$info['evaluate'];
            $this->season_id=$info['season_id'];
            $this->sum=$info['sum'];
        }
    }

}

class Functions{

    /**
     * 求百分比
     * @param $str1
     * @param $str2
     * @return float|int
     */
    static public function percent($str1,$str2)
    {
        if(is_numeric($str1) && is_numeric($str2)) return $str1/$str2*100;
        else if ($str1=="没有记录!") return 0;
        else return 100;
    }
}