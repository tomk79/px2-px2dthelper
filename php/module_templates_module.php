<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * module_templates_module.php
 */
class module_templates_module{

	/**
	 * Picklesオブジェクト
	 */
	private $px;

	/**
	 * px2dthelper main
	 */
	private $main;

	/**
	 * Pickles2 Desktop Tool config
	 */
	private $px2dtconf;

	/**
	 * module templtes
	 */
	private $modTpls;

	/**
	 * module ID
	 */
	private $mod_id;

	/**
	 * template source
	 */
	private $template;

	/**
	 * module info
	 */
	private $info = array();

	/**
	 * README.md
	 */
	private $readme = '';

	/**
	 * 編集フィールド情報
	 */
	private $fields = array();

	/**
	 * sub modules
	 */
	private $sub_modules = array();

	/**
	 * モジュール個別の名前領域
	 */
	private $name_space = null;

	/**
	 * 最上位モジュール(サブモジュールにセットされる)
	 */
	private $modTop = null;

	/**
	 * sub module name (サブモジュールにセットされる)
	 */
	private $sub_mod_name = null;

	/**
	 * constructor
	 * 
	 * @param object $px $pxオブジェクト
	 * @param object $main main.php のインスタンス
	 * @param string $mod_id モジュールID
	 * @param string $mod_path モジュールのパス
	 * @param object $options オプション
	 * @param object $modTop 最上位モジュールオブジェクト (self のインスタンス)
	 */
	public function __construct( $px, $main, $mod_id, $mod_path, $options = null, $modTop = null ){
		$this->px = $px;
		$this->main = $main;

		$this->px2dtconf = $this->main->get_px2dtconfig();
		$this->modTpls = $this->main->module_templates();

		$this->mod_id = $mod_id;

		if( strlen( $mod_path ) && is_dir( $mod_path ) ){
			$this->template = $this->px->fs()->read_file( $mod_path.'/template.html' );
			$this->info = json_decode('{}');
			if( $this->px->fs()->is_file( $mod_path.'/info.json' ) ){
				$this->info = json_decode( $this->px->fs()->read_file( $mod_path.'/info.json' ) );
			}
			if( $this->px->fs()->is_file( $mod_path.'/README.html' ) ){
				$this->readme = $this->px->fs()->read_file( $mod_path.'/README.html' );
			}elseif( $this->px->fs()->is_file( $mod_path.'/README.md' ) ){
				$this->readme = \Michelf\MarkdownExtra::defaultTransform( $this->px->fs()->read_file( $mod_path.'/README.md' ) );
			}
		}

		// 特殊なシステムモジュール
		if( $this->mod_id == '_sys/root' ){
			$this->template = '{&{"module":{"name":"main"}}&}';

		}elseif( $this->mod_id == '_sys/unknown' ){
			$this->template = '<div style="background:#f00;padding:10px;color:#fff;text-align:center;border:1px solid #fdd;">[ERROR] 未知のモジュールテンプレートです。<!-- .error --></div>';

		}elseif( $this->mod_id == '_sys/html' ){
			$this->template = '{&{"input":{"type":"html","name":"main"}}&}';

		}

		$this->sub_modules = [];
		$this->fields = array();

		if( is_object( $options ) && strlen($options->subModName) ){
			$this->template = $options->src;
			$this->sub_mod_name = $options->subModName;
		}
		$this->set_mod_top( @$modTop );

		$this->parse( $this->template );
	}

	/**
	 * 最上位モジュールオブジェクトをセット
	 * @param object $modTop 最上位オブジェクト (省略時 $this を適用)
	 * @return bool true
	 */
	private function set_mod_top( $modTop = null ){
		if( is_object( $modTop ) ){
			$this->modTop = $modTop;
			return true;
		}
		$this->modTop = $this;
		return true;
	}

	/**
	 * 最上位モジュールオブジェクトを取得
	 * @return object 最上位モジュールオブジェクト
	 */
	private function get_mod_top(){
		if( strlen( $this->sub_mod_name ) ){
			return $this->modTop;
		}
		return $this;
	}

	/**
	 * モジュールをパースする
	 */
	private function parse( $src ){
		while( 1 ){
			if( !preg_match( '/^(.*?)\\{\\&(.*?)\\&\\}(.*)$/si', $src, $matched ) ){
				break;
			}
			$field = json_decode(trim($matched[2]));
			$src = $matched[3];

			if( @$field->input ){
				$this->fields[$field->input->name] = $field->input;
				$this->fields[$field->input->name]->fieldType = 'input';

			}elseif( @$field->module ){
				$this->fields[$field->module->name] = $field->module;
				$this->fields[$field->module->name]->fieldType = 'module';

			}elseif( @$field->loop ){
				$this->fields[$field->loop->name] = $field->loop;
				$this->fields[$field->loop->name]->fieldType = 'loop';

				$tmpSearchResult = $this->search_end_tag( $src, 'loop' );
				if( !is_array( $this->sub_modules ) ){
					$this->sub_modules = [];
				}
				@$this->get_mod_top()->sub_modules[@$field->loop->name] = new self(
					$this->px,
					$this->main,
					$this->get_id(),
					null,
					json_decode( json_encode([
						"src"=>$tmpSearchResult->content,
						"subModName"=>$field->loop->name
					]) ) ,
					$this->get_mod_top()
				);
				$src = $tmpSearchResult->nextSrc;

			}elseif( @$field->if ){

			}elseif( @$field->echo ){

			}

			continue;
		}

		return true;
	}

	/* 閉じタグを探す */
	private function search_end_tag( $src, $fieldType ){
		$rtn = json_decode('{
			"content": "",
			"nextSrc": ""
		}');
		$rtn->nextSrc = $src;

		$depth = 0;
		while( 1 ){
			if( !preg_match( '/^(.*?)\\{\\&(.*?)\\&\\}(.*)$/is', $rtn->nextSrc, $matched ) ){
				break;
			}
			$rtn->content .= $matched[1];
			$fieldSrc = trim($matched[2]);
			$field = json_decode( $fieldSrc );
			$rtn->nextSrc = $matched[3];

			if( $field == 'end'.$fieldType ){
				if( $depth ){
					$depth --;
					$rtn->content .= '{&'.$fieldSrc.'&}';
					continue;
				}
				return $rtn;
			}else if( @$field->{$fieldType} ){
				$depth ++;
				$rtn->content .= '{&'.$fieldSrc.'&}';
				continue;
			}else{
				$rtn->content .= '{&'.$fieldSrc.'&}';
				continue;
			}
		}
		return $rtn;
	}

	/**
	 * モジュールIDを取得する
	 */
	public function get_id(){
		return $this->mod_id;
	}

	/**
	 * info.json の内容を取得する
	 */
	public function get_info(){
		return $this->info;
	}

	/**
	 * README.md の内容を取得する
	 */
	public function get_readme(){
		return $this->readme;
	}

	/**
	 * モジュール名称を取得する
	 */
	public function get_name(){
		if( @strlen( $this->info->name ) ){
			return $this->info->name;
		}
		return $this->mod_id;
	}

	/**
	 * テンプレートソースをそのまま取得する
	 */
	public function get_template(){
		return $this->template;
	}

	/**
	 * サブモジュールを取得する
	 */
	public function get_sub_module( $sub_module_name ){
		return @$this->sub_modules[ $sub_module_name ];
	}

	/**
	 * sub_mod_name を取得する
	 * 自身がサブモジュールではない場合、nullが返ります。
	 */
	public function get_sub_mod_name(){
		return $this->sub_mod_name;
	}

	/**
	 * 名前領域に値をセット
	 */
	public function set_name_space( $name, $val ){
		if( !is_object( $this->name_space ) ){ $this->name_space = new \stdClass(); }
		if( !is_array( @$this->name_space->val ) ){ $this->name_space->val = array(); }
		@$this->name_space->val[ $name ] = $val;
		if( $this->name_space->val[ $name ] !== $val ){
			return false;
		}
		return true;
	}

	/**
	 * 名前領域から値を取得
	 */
	public function get_name_space( $name ){
		if( !is_object( $this->name_space ) ){ $this->name_space = new \stdClass(); }
		if( !is_array( $this->name_space->val ) ){ $this->name_space->val = array(); }
		return @$this->name_space->val[ $name ];
	}


	/**
	 * モジュールにデータをバインドして返す
	 */
	public function bind( $data, $mode = 'finalize' ){
		$src = $this->template;
		$rtn = '';
		while( 1 ){
			if( !preg_match( '/^(.*?)\\{\\&(.*?)\\&\\}(.*)$/si', $src, $matched ) ){
				$rtn .= $src;
				break;
			}
			$rtn .= $matched[1];
			$field = json_decode(trim($matched[2]));
			$src = $matched[3];

			if( @$field->input ){
				// input field
				$tmpVal = '';

				$tmpVal .= $this->main->get_field_definition( $field->input->type )->bind( $data->fields->{@$field->input->name}, $mode );

				if( !@$field->input->hidden ){//← "hidden": true だったら、非表示(=出力しない)
					$rtn .= $tmpVal;
				}
				$this->get_mod_top()->set_name_space( @$field->input->name, json_decode(json_encode([
					"fieldType"=>"input",
					"type"=>$field->input->type,
					"val"=>$tmpVal
				])) );


			}elseif( @$field->module ){
				foreach( $data->fields->{$field->module->name} as $tmp_data ){
					$rtn .= $this->main->module_templates()->get( $tmp_data->modId )->bind( $tmp_data );
				}

			}elseif( @$field->loop ){
				$tmpSearchResult = $this->search_end_tag( $src, 'loop' );
				foreach( $data->fields->{$field->loop->name} as $tmp_data ){
					$rtn .= $this->get_sub_module( $field->loop->name )->bind( $tmp_data );
				}
				$src = $tmpSearchResult->nextSrc;

			}elseif( @$field->if ){
				// if field
				// is_set に指定されたフィールドに値があったら、という評価ロジックを取り急ぎ実装。
				// もうちょっとマシな条件の書き方がありそうな気がするが、あとで考える。
				$tmpSearchResult = $this->search_end_tag( $src, 'if' );
				$src = '';
				if( @$this->get_mod_top()->get_name_space( @$field->if->is_set ) && strlen(trim(@$this->get_mod_top()->get_name_space( @$field->if->is_set )->val)) ){
					$src .= $tmpSearchResult->content;
				}
				$src .= $tmpSearchResult->nextSrc;

			}elseif( @$field->echo ){
				// echo field
				if( @$this->get_mod_top()->get_name_space( @$field->echo->ref ) && @$this->get_mod_top()->get_name_space( @$field->echo->ref )->val ){
					$rtn .= $this->get_mod_top()->get_name_space( @$field->echo->ref )->val;
				}

			}

			continue;
		}
		return $rtn;
	}

}
