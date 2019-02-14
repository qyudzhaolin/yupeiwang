<?php
//文件上传
class b2c_Think_upload {
    /**
     * 是否上传文件
     * @return boolean
     */
    public function _is_uploaded_file($filename)
    {
        $upfiles = isset($filename) ? $_FILES[$filename]['tmp_name'] : $_FILES['file']['tmp_name'];
        if (is_array($upfiles)) {//如果是多个文件
            foreach ($upfiles as $upfile) {
                if($upfile) return true;
            }
            return false;
        }
        if(!empty($upfiles)){//单个文件
            return true;
        }
        return false;
    }


    /**
    * 上传图并缩略图片
    * @param array $configs 配置数组
    * @param array $thumbSize 配置在config下面的缩略图尺寸
    * @param string $folder 保存路径
    * @param int $maxNum 最多上传数量，默认6张图
    * @return array array('s'=>原图,'a'=>大图,'b'=>中图片,'c'=>小图,);
    */
    public function _uploadsImages($configs= array())
    {
        $configs['folder'] = isset($configs['folder']) ? $configs['folder'] : 'Images';
        $configs['isThumb'] = isset($configs['isThumb']) ? $configs['isThumb'] : false;
        $configs['noLimitSize'] = isset($configs['noLimitSize']) ? $configs['noLimitSize'] : false;
        $configs['maxNum'] = $maxNum = isset($configs['maxNum']) ? $configs['maxNum'] : 6;
        $configs['thumbSize'] = isset($configs['thumbSize']) ? $configs['thumbSize'] : 'thumbImage';//缩略图数组尺寸配置
        $configs['files'] = isset($configs['files']) ? array($configs['files'] => $_FILES[$configs['files']]) : '';//文件信息数组 $files ，通常是 $_FILES数组
        // ee($configs);

        require_once getConfig('Think') . 'uploadBase.php';
        $upload = new uploadBase();
        $upload->maxSize = $configs['noLimitSize'] ? 10485760*30 : 10485760;//1024字节*1024K*10M=10485760字节
        // $upload->subName = array('get_day', 'monday');                  //子目录保存
        $upload->subName = array('date', 'Ymd');                  //子目录保存
        $upload->exts    = array('jpg', 'gif', 'png', 'jpeg', 'apk');
        $upload->saveName =  array('make_sn', '');                  //文件名
        $upload->rootPath = './public/files/Uploads/' . $configs['folder'] . '/';
        $infos   =   $upload->upload($configs['files']);
        $result = array();

        if(!$infos) {
            $errorType = "文件错误(" . $configs['files'] . ')：';
            $this->apiReturn(array('error' => 1, 'message' => $errorType . $upload->getError()));
        }else{

           //开始缩略图
           foreach ($infos as &$info) {
               $images = array();
               $info['saveFile'] = $info['savepath'] . $info['savename'];
               $file = $info['fullName'] = $upload->rootPath . $info['saveFile'];

               //默认是不缩略的
               if($configs['isThumb']){
                   $images = $this->_thumbs($file, $configs['thumbSize']);
                   $images['s'] = getConfig('domain') . trim($file, '.');//原图
                   $result['data'][] = $images;//返回给前端使用
               }
               $result['info'][] = $info;//后台使用，保存数据库
           }
           return $result;
        }
    }

    /**
    * 获取前端上传好的文件数组信息进行缩略图处理(一般是后台返回的所有上传信息集合)
    * @param string $dir 目录名称
    * @return array 返回处理过的上传的文件数组(json => array)
    */
    public function thumbUploadedFiles($files, $isThumb = true){
        $files = !empty($files) ? $files : trim($_REQUEST['files']);
        if(empty($files)){
            return null;
        }
        $files = json_decode($files,true);
        if(!is_array($files) || empty($files)){
            $this->apiReturn(array('error' => 1, 'message' => "上传后的数据转换失败01，请联系管理员！"));
        }
        foreach ($files as $key => &$file) {
            if(empty($file)) continue;
            foreach ($file as &$f) {
                if(empty($f) || empty($f['fullName']) || !$isThumb) continue;
                $this->_thumbs($f['fullName'], 'thumbImage');//开始缩略图处理
            }
        }
        return $files;
    }

    /**
    * 缩略图,默认保存在该文件对应的目录下面,后缀从配置的K中取值
    * @param string $file 要处理的图片
    * @param array $thumbSize 配置在config下面的缩略图尺寸
    * @return boolean
    */
    protected function _thumbs($file, $thumbSize)
    {
        require_once getConfig('Think') . 'Image.php';
        $image = new Image();

        $thumbs = getConfig($thumbSize);
        $info = pathinfo($file);
        $result = array();
        if(!$thumbs){
            $this->apiReturn(array('error' => 1, 'message' => '没有配置缩略图信息(大中小尺寸)！'));
        }
        foreach ($thumbs as $k => $th) {
            $image->open($file);
            // ee($image);
            $fileName = $info['dirname'] . '/' . $info['filename'] . '_' . $k . '.' . $info['extension'];
            $result[$k] = getConfig('domain') . trim($fileName, '.');
            $image->thumb($th['width'], $th['height'])->save($fileName);
        }
        return $result;

    }


    /**
    * 获取a\b\c\d尺寸缩略图路径，传数组返回数组
    * @param string $pic 要传递的数据库图片字段
    * @param string $path 扩展目录名称
    * @param string $isSite 是否只是用于展示(如果不是用于后台删除图片，而只是用于前台展示需要去掉.)
    * @return boolean
    */
    public function getImageSizes($image,$path='',$isSite=0){//这个path是索引
        $images = array();
        $preFix = $isSite ? '' : '.';
        $images['s'] = $preFix . '/public/files/Uploads/' . strtolower($path) . '/' . $image;
        //缩略图
        $images['a'] = preg_replace("/(\.\w+)$/", '_a$1', $images['s']);//大
        $images['b'] = preg_replace("/(\.\w+)$/", '_b$1', $images['s']);//中
        $images['c'] = preg_replace("/(\.\w+)$/", '_c$1', $images['s']);//小
        return $images;
    }


    /**
     * [返回json格式数据]
     * @param  [array] $data 数组
     * @param  [int] $data['error'] 状态值 不传值或传0则为成功状态
     * @param  [int] $data['message'] 消息 不传值默认为‘操作成功提示’
     * @param  [int] $data['data'] 为实体数据
     * @return [json]  [返回数据]
     */
    function apiReturn($data = array()){
        $data['error']   = isset($data['error']) ? $data['error'] : 0;
        $data['message'] = isset($data['message']) ? $data['message'] : '操作成功！';

        $data['data']    = isset($data['data']) ? $data['data'] : null;
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    }


    /**
     * 显示上传的缩略图到DOM中
     * @param  [array] $files  [从图片数据库取得的行数据]
     * @param  string $type [删除类型,在controller,setting/imagedel统一定义]
     * @param  string $item_id [图片一对多的主键sn]
     * @param  string $t [数据表名称，用于删除动作使用]
     * @param  bool $isSetCover [是否开启默认图功能,默认是开启的]
     * @return [html]          [返回数据]
     */
    function showUploadedThumbFiles($files, $type='images',$item_id,$isdel=1, $t='images', $isSetCover = false){
        $html = '';
        if(empty($files)) return '';
        $setCover = '';
        $delTab = $isdel ? '' : 'hide';
        foreach ($files as $file) {
            //设置默认图
            if ($isSetCover) {
                $showCoverPic = $file['is_default'] == 1 ? " visible" : ' noVisible';
                $hiddeSetdefault = $file['is_default'] == 1 ? " noVisible" : '';
                $setCover = "<div onclick=\"manage.setCover({dom:this,item_id:'{$item_id}',t:'{$t}'})\" class=\"info setDef setcover{$hiddeSetdefault}\">设置默认图</div>";
                $setCover .= "<div onclick=\"manage.setCover({dom:this,isRemove:'1',t:'{$t}'})\" class=\"info removeDef setcover{$showCoverPic}\">取消默认</div>";
            }

            $html .= "<div id=\"{$item_id}\" class=\"file-item thumbnail\">
                        <img title=\"点击放大\" class=\"s50\" onerror=\"manage.imgerror(this)\" src=\"{$file['b']}\">
                        <div onclick=\"manage.delImage({dom:this,type:'{$type}'})\" class=\"info cancel {$delTab}\">×</div>
                        {$setCover}
                    </div>";
        }
        return $html;
    }

}

//生成SN
function make_sn(){
    return md5(uniqid(mt_rand(0,99999999),1));
}
