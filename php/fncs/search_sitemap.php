<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * fncs/search_sitemap.php
 */
class fncs_search_sitemap{

	/** Picklesオブジェクト */
	private $px;

	/** px2dthelperオブジェクト */
	private $px2dthelper;

	/**
	 * constructor
	 *
	 * @param object $px2dthelper $px2dthelperオブジェクト
	 * @param object $px $pxオブジェクト
	 */
	public function __construct( $px2dthelper, $px ){
		$this->px2dthelper = $px2dthelper;
		$this->px = $px;
	}

	/**
	 * サイトマップ中のページを検索する
	 *
	 * @param  string $keyword キーワード
	 * @param  array $options オプション
	 * @return array 検出されたページの一覧
	 */
	public function find( $keyword, $options = array() ){
		$rtn = array();
		if( !$this->px->site() ){
			return array();
		}
		if(!is_array($options)){
			$options = array();
		}

		$sitemap = $this->px->site()->get_sitemap();
		foreach($sitemap as $page){
			foreach($page as $key=>$val){
				if( @preg_match('/'.preg_quote($keyword, '/').'/s', $val) ){
					array_push($rtn, $page);
					continue 2;
				}
			}
		}

		return $rtn;
	}

}
