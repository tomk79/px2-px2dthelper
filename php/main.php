<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * main.php
 */
class main{

	/**
	 * Picklesオブジェクト
	 */
	private $px;

	/**
	 * PXコマンド名
	 */
	private $command = array();

	/**
	 * px2dtconfig
	 */
	private $px2dtconfig;

	/**
	 * $module_templates
	 */
	private $obj_module_templates;

	/**
	 * entry
	 */
	static public function register($px){
		$px->pxcmd()->register('px2dthelper', function($px){
			(new self( $px ))->kick();
			exit;
		}, true);
	}

	/**
	 * px2-px2dthelper のバージョン情報を取得する。
	 * 
	 * <pre> [バージョン番号のルール]
	 *    基本
	 *      メジャーバージョン番号.マイナーバージョン番号.リリース番号
	 *        例：1.0.0
	 *        例：1.8.9
	 *        例：12.19.129
	 *      - 大規模な仕様の変更や追加を伴う場合にはメジャーバージョンを上げる。
	 *      - 小規模な仕様の変更や追加の場合は、マイナーバージョンを上げる。
	 *      - バグ修正、ドキュメント、コメント修正等の小さな変更は、リリース番号を上げる。
	 *    開発中プレビュー版
	 *      基本バージョンの後ろに、alpha(=α版)またはbeta(=β版)を付加し、その連番を記載する。
	 *        例：1.0.0-alpha1 ←最初のα版
	 *        例：1.0.0-beta12 ←12回目のβ版
	 *      開発中およびリリースバージョンの順序は次の通り
	 *        1.0.0-alpha1 -> 1.0.0-alpha2 -> 1.0.0-beta1 ->1.0.0-beta2 -> 1.0.0 ->1.0.1-alpha1 ...
	 *    ナイトリービルド
	 *      ビルドの手順はないので正確には "ビルド" ではないが、
	 *      バージョン番号が振られていない、開発途中のリビジョンを
	 *      ナイトリービルドと呼ぶ。
	 *      ナイトリービルドの場合、バージョン情報は、
	 *      ひとつ前のバージョン文字列の末尾に、'-nb' を付加する。
	 *        例：1.0.0-beta12-nb (=1.0.0-beta12リリース後のナイトリービルド)
	 *      普段の開発においてコミットする場合、
	 *      必ずこの get_version() がこの仕様になっていることを確認すること。
	 * </pre>
	 * 
	 * @return string バージョン番号を示す文字列
	 */
	public function get_version(){
		return '2.0.0-alpha1-nb';
	}

	/**
	 * constructor
	 * 
	 * @param object $px $pxオブジェクト
	 */
	public function __construct( $px ){
		$this->px = $px;
		$this->px2dtconfig = json_decode('{}');
		if( @is_object($this->px->conf()->plugins->px2dt) ){
			$this->px2dtconfig = $this->px->conf()->plugins->px2dt;
		}elseif( is_file( $this->px->get_path_homedir().'px2dtconfig.json' ) ){
			$this->px2dtconfig = json_decode( $this->px->fs()->read_file( $this->px->get_path_homedir().'px2dtconfig.json' ) );
		}

		require_once( __DIR__.'/module_templates.php' );
		$this->obj_module_templates = new module_templates($this->px, $this);

	}

	/**
	 * px2dtconfigを取得する
	 */
	public function get_px2dtconfig(){
		return $this->px2dtconfig;
	}

	/**
	 * ドキュメントモジュール定義をロードする
	 */
	public function document_modules(){
		require_once( __DIR__.'/document_modules.php' );
		$rtn = '';
		$rtn = new document_modules($this->px, $this);
		return $rtn;
	}

	/**
	 * フィールド定義オブジェクトを取得
	 */
	public function get_field_definition( $field_type ){
		require_once( __DIR__.'/field_base.php' );
		$rtn = null;
		if( is_file( __DIR__.'/fields/field.'.$field_type.'.php' ) ){
			require_once( __DIR__.'/fields/field.'.$field_type.'.php' );
			$class_name = '\\tomk79\\pickles2\\px2dthelper\\field_'.$field_type;
			$rtn = new $class_name();
		}else{
			$rtn = new field_base();
		}
		return $rtn;
	}

	/**
	 * ドキュメントモジュール定義をロードする
	 */
	public function module_templates(){
		return $this->obj_module_templates;
	}

	/**
	 * kick as PX Command
	 * 
	 * @return void
	 */
	private function kick(){
		$this->command = $this->px->get_px_command();

		switch( @$this->command[1] ){
			case 'ping':
				// 疎通確認応答
				$this->fnc_ping();
				break;
		}

		print $this->px->pxcmd()->get_cli_header();
		print 'Pickles 2 Desktop Tool Helper plugin.'."\n";
		print $this->px->pxcmd()->get_cli_footer();
		exit;
	}

	/**
	 * 疎通確認応答
	 * 
	 * @return void
	 */
	private function fnc_ping(){
		header('Content-type: text/plain;');
		print 'ok'."\n";
		exit;
	}

}
