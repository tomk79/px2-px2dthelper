<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class initContentTest extends PHPUnit\Framework\TestCase{

	private $fs;
	private $px2query;

	/**
	 * setup
	 */
	public function setup() : void{
		set_time_limit(60);
		$this->fs = new \tomk79\filesystem();
		require_once(__DIR__.'/testHelper/pickles2query.php');
		$this->px2query = new testHelper_pickles2query();
	}

	/**
	 * コンテンツファイル初期化のテスト
	 */
	public function testInitializeContent(){

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/html/test.html?PX=px2dthelper.init_content'
		] );
		$output = json_decode($output);
		$this->assertEquals( gettype(array()), gettype($output) );
		$this->assertEquals( $output[0], true );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html/test.html' ) );

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/html/?PX=px2dthelper.init_content'
		] );
		$output = json_decode($output);
		$this->assertEquals( gettype(array()), gettype($output) );
		$this->assertEquals( $output[0], true );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html/index.html' ) );

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/html.gui/?PX=px2dthelper.init_content&editor_mode=html.gui'
		] );
		$output = json_decode($output);
		$this->assertEquals( gettype(array()), gettype($output) );
		$this->assertEquals( $output[0], true );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html.gui/index.html' ) );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html.gui/index_files/guieditor.ignore/data.json' ) );

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/md/?PX=px2dthelper.init_content&editor_mode=md'
		] );
		$output = json_decode($output);
		$this->assertEquals( gettype(array()), gettype($output) );
		$this->assertEquals( $output[0], true );
		$this->assertEquals( $output[1], 'ok' );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/init_content/md/index.html.md' ) );


		// forceフラグのテスト
		$this->assertSame( 0, filesize( __DIR__.'/testData/standard/init_content/md/index.html.md' ) );

		file_put_contents(__DIR__.'/testData/standard/init_content/md/index.html.md', 'teststring');
		clearstatcache();

		$this->assertSame( 10, filesize( __DIR__.'/testData/standard/init_content/md/index.html.md' ) );

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/md/?PX=px2dthelper.init_content&editor_mode=html'
		] );
		$output = json_decode($output);
		$this->assertEquals( gettype(array()), gettype($output) );
		$this->assertEquals( $output[0], false );
		$this->assertEquals( $output[1], 'Contents already exists.' );
		$this->assertFalse( is_file( __DIR__.'/testData/standard/init_content/md/index.html' ) );
		$this->assertSame( 10, filesize( __DIR__.'/testData/standard/init_content/md/index.html.md' ) );

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/md/?PX=px2dthelper.init_content&editor_mode=html&force=1'
		] );
		$output = json_decode($output);
		$this->assertEquals( gettype(array()), gettype($output) );
		$this->assertEquals( $output[0], true );
		$this->assertEquals( $output[1], 'ok' );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/init_content/md/index.html' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/standard/init_content/md/index.html.md' ) );
		$this->assertSame( 0, filesize( __DIR__.'/testData/standard/init_content/md/index.html' ) );



		// 後始末
		$this->fs->rm(__DIR__.'/testData/standard/init_content/');
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	}

	/**
	 * コンテンツテンプレートのテスト
	 */
	public function testContentsTemplate(){
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.contents_template.get_list'
		] );
		$output = json_decode($output);
		$this->assertTrue( is_object($output) );
		$this->assertEquals( $output->default, 'html.gui' );
		$this->assertEquals( count($output->list), 3 );
		$this->assertEquals( $output->list[0]->id, 'html.gui' );
		$this->assertEquals( $output->list[0]->name, 'ブロックエディタ' );
		$this->assertEquals( $output->list[0]->type, 'html.gui' );
		$this->assertEquals( $output->list[0]->thumb, null );
		$this->assertEquals( $output->list[1]->id, 'html' );
		$this->assertEquals( $output->list[1]->type, 'html' );
		$this->assertEquals( $output->list[1]->thumb, null );
		$this->assertEquals( $output->list[2]->id, 'md' );
		$this->assertEquals( $output->list[2]->type, 'md' );
		$this->assertEquals( $output->list[2]->thumb, null );

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.contents_template.get_list&lang=en'
		] );
		$output = json_decode($output);
		$this->assertTrue( is_object($output) );
		$this->assertEquals( $output->default, 'html.gui' );
		$this->assertEquals( count($output->list), 3 );
		$this->assertEquals( $output->list[0]->id, 'html.gui' );
		$this->assertEquals( $output->list[0]->name, 'Block editor' );
		$this->assertEquals( $output->list[0]->type, 'html.gui' );
		$this->assertEquals( $output->list[0]->thumb, null );
		$this->assertEquals( $output->list[1]->id, 'html' );
		$this->assertEquals( $output->list[1]->type, 'html' );
		$this->assertEquals( $output->list[1]->thumb, null );
		$this->assertEquals( $output->list[2]->id, 'md' );
		$this->assertEquals( $output->list[2]->type, 'md' );
		$this->assertEquals( $output->list[2]->thumb, null );

		$output = $this->px2query->query( [
			__DIR__.'/testData/px2dt_config/.px_execute.php',
			'/?PX=px2dthelper.contents_template.get_list'
		] );
		$output = json_decode($output);
		$this->assertTrue( is_object($output) );
		$this->assertEquals( $output->default, 'broccoli' );
		$this->assertEquals( count($output->list), 4 );
		$this->assertEquals( $output->list[0]->id, 'broccoli' );
		$this->assertEquals( $output->list[0]->type, 'html.gui' );
		$this->assertEquals( $output->list[0]->thumb, null );
		$this->assertEquals( $output->list[1]->id, 'html' );
		$this->assertEquals( $output->list[1]->type, 'html' );
		$this->assertEquals( $output->list[1]->thumb, null );
		$this->assertEquals( $output->list[2]->id, 'md' );
		$this->assertEquals( $output->list[2]->type, 'md' );
		$this->assertEquals( preg_match('/^data\:image\/gif\;base64\,/', $output->list[2]->thumb), 1 );
		$this->assertEquals( $output->list[3]->id, 'md_article' );
		$this->assertEquals( $output->list[3]->type, 'md' );
		$this->assertEquals( $output->list[3]->thumb, null );

		// テンプレートからコンテンツを初期化する
		$output = $this->px2query->query( [
			__DIR__.'/testData/px2dt_config/.px_execute.php',
			'/init_content/html/test.html?PX=px2dthelper.init_content&editor_mode=broccoli'
		] );
		$output = json_decode($output);
		$this->assertEquals( $output[0], true );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/px2dt_config/init_content/html/test.html' ) );
		$this->assertTrue( $this->fs->is_dir( __DIR__.'/testData/px2dt_config/init_content/html/test_files/' ) );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/px2dt_config/init_content/html/test_files/guieditor.ignore/data.json' ) );



		// 後始末
		$this->fs->rm(__DIR__.'/testData/px2dt_config/init_content/');
		$output = $this->px2query->query( [
			__DIR__.'/testData/px2dt_config/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	}

}
