<?php

require_once(dirname(__FILE__).'/../config.php');

class Image_TestCase extends ActiveSupportUnitTest
{
    public function __construct()
    {
        parent::__construct();
        if(!($this->offline_mode = !(@file_get_contents('http://www.akelos.org/testing_resources/images/watermark.png')))){
            $this->image_path = AkConfig::getDir('fixtures').'/Image_TestCase/akelos_framework_logo.png';
            $this->photo_path = AkConfig::getDir('fixtures').'/Image_TestCase/cristobal.jpg';
            $this->watermark = AkConfig::getDir('fixtures').'/Image_TestCase/watermark.png';

            Ak::copy(AkConfig::getDir('fixtures').'/old_logo.png', $this->image_path);
            $cristobal = @Ak::url_get_contents('http://www.akelos.org/testing_resources/images/cristobal.jpg', array('cache'=>100000));
            if(!empty($cristobal)) Ak::file_put_contents($this->photo_path, $cristobal);
            $watermark = @Ak::url_get_contents('http://www.akelos.org/testing_resources/images/watermark.png', array('cache'=>100000));
            if(!empty($watermark)) Ak::file_put_contents($this->watermark, $watermark);
            $this->_run_extra_tests = file_exists($this->photo_path);
        }
    }

    public function __destruct()
    {        
        Ak::directory_delete(AkConfig::getDir('fixtures').'/Image_TestCase');
    }

    public function skip(){
        $this->skipIf($this->offline_mode, '['.get_class($this).'] Internet connection unavailable.');
    }

    public function test_image_save_as()
    {
        $PngImage = new AkImage($this->image_path);
        $this->assertEqual($PngImage->getExtension(), 'png');

        $PngImage->save($this->image_path.'.jpg');
        $JpgImage = new AkImage($this->image_path.'.jpg');
        $this->assertEqual($JpgImage->getExtension(), 'jpg');

        $PngImage = new AkImage($this->image_path);
        $PngImage->save($this->image_path.'.gif');
        $GifImage = new AkImage($this->image_path.'.gif');
        $this->assertEqual($GifImage->getExtension(), 'gif');
    }

    public function test_image_resize()
    {
        $Image = new AkImage();
        $Image->load($this->image_path);

        $this->assertEqual($Image->getWidth(), 170);
        $this->assertEqual($Image->getHeight(), 75);


        $Image->transform('resize',array('size'=>'50x'));
        $Image->save($this->image_path.'_50x22.jpg');

        $Image = new AkImage($this->image_path.'_50x22.jpg');
        $this->assertEqual($Image->getWidth(), 50);
        $this->assertEqual($Image->getHeight(), 22);


        $Image = new AkImage($this->image_path);
        $Image->transform('resize',array('size'=>'50%'));
        $Image->save($this->image_path.'_85x37.png');

        $Image = new AkImage($this->image_path.'_85x37.png');
        $this->assertEqual($Image->getWidth(), 85);
        $this->assertEqual($Image->getHeight(), 37);


        $Image = new AkImage($this->image_path);
        $Image->transform('resize', array('mode'=>'force','size'=>'300x300'));
        $Image->save($this->image_path.'_300x300.png');

        $Image = new AkImage($this->image_path.'_300x300.png');
        $this->assertEqual($Image->getWidth(), 300);
        $this->assertEqual($Image->getHeight(), 300);


        $Image = new AkImage($this->image_path);
        $Image->transform('resize', array('mode'=>'expand','size'=>'x300'));
        $Image->save($this->image_path.'_x300.png');

        $Image = new AkImage($this->image_path.'_x300.png');
        $this->assertEqual($Image->getWidth(), 680);
        $this->assertEqual($Image->getHeight(), 300);



        $Image = new AkImage($this->image_path);
        $Image->transform('resize', array('mode'=>'expand','size'=>'300x300'));
        $Image->save($this->image_path.'_680x300.png');

        $Image = new AkImage($this->image_path.'_680x300.png');
        $this->assertEqual($Image->getWidth(), 680);
        $this->assertEqual($Image->getHeight(), 300);


        $Image = new AkImage($this->image_path);
        $Image->transform('resize', array('mode'=>'expand','size'=>'200%'));
        $Image->save($this->image_path.'_340x150.png');

        $Image = new AkImage($this->image_path.'_340x150.png');
        $this->assertEqual($Image->getWidth(), 340);
        $this->assertEqual($Image->getHeight(), 150);
    }


    public function test_image_crop()
    {
        if(!$this->_run_extra_tests) return;

        $Image = new AkImage();
        $Image->load($this->photo_path);

        $Image->transform('crop',array('x'=>20, 'y'=>0, 'size'=>'30x30'));
        $Image->save($this->photo_path.'_30x30_crop.jpg');

        $Image = new AkImage($this->photo_path.'_30x30_crop.jpg');
        $this->assertEqual($Image->getWidth(), 30);
        $this->assertEqual($Image->getHeight(), 30);

        $Image = new AkImage();
        $Image->load($this->photo_path);

        $Image->transform('crop',array('x'=>20, 'y'=>15, 'width'=>50));
        $Image->save($this->photo_path.'_50_crop.jpg');

        $Image = new AkImage($this->photo_path.'_50_crop.jpg');
        $this->assertEqual($Image->getWidth(), 50);
        $this->assertEqual($Image->getHeight(), 359);

        $Image = new AkImage();
        $Image->load($this->photo_path);

        $Image->transform('crop',array('x'=>0, 'y'=>15));
        $Image->save($this->photo_path.'top_crop.jpg');

        $Image = new AkImage($this->photo_path.'top_crop.jpg');
        $this->assertEqual($Image->getWidth(), 499);
        $this->assertEqual($Image->getHeight(), 359);
    }

    public function test_image_watermark()
    {
        if(!$this->_run_extra_tests) return;

        $Image = new AkImage();
        $Image->load($this->photo_path);
        $Image->transform('watermark',array('mark'=>$this->watermark));
        $Image->save($this->photo_path.'_watermarked.jpg');
        $this->assertEqual(md5_file($this->photo_path.'_watermarked.jpg'), '234adf4a48224f8596e53d665bf41768');
    }

    public function test_should_apply_native_filters()
    {
        $native_filters = array(
        'negate' =>         array('params' => array(), 'hash' => '8b44f26c9646ac69a1b48bbc66622184'),
        'grayscale' =>      array('params' => array(), 'hash' => 'd08a0ad61f4fd5b343c0a4af6d810ddf'),
        'brightness' =>     array('params' => 50, 'hash' => '1e38de2377e42848cae326de52a75252'),
        'contrast' =>       array('params' => 50, 'hash' => 'ded57ff56253afb0efd4e09c17d44efb'),
        'colorize' =>       array('params' => array(100,25,30), 'hash' => 'ddcb214d2e9c0c6c7d58a9bb0ce09b4a'),
        'detect_edges' =>   array('params' => array(), 'hash' => '4c5f8c9f54917b66ecea8631aabb0e85'),
        'emboss' =>         array('params' => array(), 'hash' => 'a3edb232afbd5d9e210172a40abec35e'),
        'gaussian_blur' =>  array('params' => array(), 'hash' => 'd1d2ba1995dff5b7c638d85d968d070a'),
        'selective_blur' => array('params' => array(), 'hash' => 'b68b972fc7d29d3a4942f2057ab085f2'),
        'sketch' =>         array('params' => array(), 'hash' => '63d0dd06515c4ec72f8dc5fc9de74d8e'),
        'smooth' =>         array('params' => 5, 'hash' => '6158f362febe3b7b9add756c9d5acf2c'),
        //'pixelate' =>       array('params' => array(30,true), 'hash' => '123')
        );

        foreach ($native_filters as $native_filter => $options){
            $Image = new AkImage();
            $Image->load($this->photo_path);
            if($Image->isNativeFiler($native_filter)){
                $Image->transform($native_filter, $options['params']);
                $image_path = $this->photo_path."_$native_filter.jpg";
                $Image->save($image_path);
                $this->assertEqual(md5_file($image_path),  $options['hash'], "$native_filter failed, we expected the checksum ".$options['hash'].' and got '.md5_file($image_path));
            }
        }
    }

}

ak_test_case('Image_TestCase');
