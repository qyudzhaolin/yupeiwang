<?php
/**
 * 说明：新加宇配公告
 */
class b2c_ctl_site_news extends b2c_frontpage{
    function __construct($app) {
        parent::__construct($app);
        $this->app = $app;
        $this->pagesize = 10;
        $this->_response->set_header('Cache-Control', 'no-store');
    }
    public function index($nPage=1){
        $mdl_article_index = app::get('content')->model('article_indexs');
        $wheres = ['node_id'=>42,'disabled'=>'false','ifpub'=>'true','platform'=>'pc'];
        $wheres['pubtime|sthan'] = time();
        $totalNum = $mdl_article_index->count($wheres);
        $aPage = $this->get_start($nPage,$totalNum);
        $rows = $mdl_article_index->getList('article_id,title,pubtime,uptime,node_id', $wheres,$aPage['start'],$this->pagesize,'pubtime desc');

        if (!empty($rows)) {
            foreach ($rows as $key => $value) {
                $rows[$key]['pubtime'] = date('Y-m-d',$value['pubtime']);
            }
        }
        $this->pagedata['rows'] = $rows;
        $this->pagination($nPage,$aPage['maxPage'],'index');
        $this->page('site/news/news.html');
    }

    public function detail($article_id=0){
        $ctime = time();
        $sql = "SELECT b.content, i.article_id, i.title,i.pubtime FROM `sdb_content_article_bodys` b LEFT JOIN `sdb_content_article_indexs` i ON b.article_id = i.article_id WHERE i.article_id = {$article_id} and i.pubtime <= {$ctime}";
        $row = kernel::database()->select($sql);
        if (!empty($row)) {
            $row = $row[0];
            $row['pubtime'] = date('Y-m-d',$row['pubtime']);
        }
        $this->pagedata['row'] = $row;
        $this->page('site/news/detail.html');
    }


    function get_start($nPage,$count){
        $maxPage = ceil($count / $this->pagesize);
        if($nPage > $maxPage) $nPage = $maxPage;
        $start = ($nPage-1) * $this->pagesize;
        $start = $start<0 ? 0 : $start;
        $aPage['start'] = $start;
        $aPage['maxPage'] = $maxPage;
        return $aPage;
    }

    /*
     *本控制器公共分页函数
     * */
    function pagination($current,$totalPage,$act,$arg='',$app_id='b2c',$ctl='site_news'){
        if (!$arg)
            $this->pagedata['pager'] = array(
                'current'=>$current,
                'total'=>$totalPage,
                'link' =>$this->gen_url(array('app'=>$app_id, 'ctl'=>$ctl,'act'=>$act,'args'=>array(($tmp = time())))),
                'token'=>$tmp,
            );
        else
        {
            $arg = array_merge($arg, array(($tmp = time())));
            $this->pagedata['pager'] = array(
                'current'=>$current,
                'total'=>$totalPage,
                'link' =>$this->gen_url(array('app'=>$app_id, 'ctl'=>$ctl,'act'=>$act,'args'=>$arg)),
                'token'=>$tmp,
            );
        }
    }

    //分类和国家下拉框
    function FCdownload(){
        $data = [];
        //热门国家推荐
        $mdl_gprovenance = kernel::single('b2c_mdl_gprovenance');
        $data['countryRows'] = $mdl_gprovenance->get_country_format(true);

        //商品分类树
        $objCat = $this->app->model('goods_cat');
        $data['catRows']=$objCat->getMap(3);

        $this->pagedata['catRows']=$data['catRows'];
        $this->pagedata['countryRows'] = $data['countryRows'];
        $this->page('site/news/FCdownload.html',true);
    }

}