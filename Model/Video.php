<?php
/**
 ************************************************************************
 * @copyright 2015 David Lima
 * @license Apache 2.0 (http://www.apache.org/licenses/LICENSE-2.0) 
 ************************************************************************
 */
namespace Fennec\Modules\Videos\Model;

use \Fennec\Model\Base;
use \Fennec\Library\PHPImageWorkshop\ImageWorkshop;

/**
 * Video model
 *
 * @author David Lima
 * @version r1.0
 */
class Video extends Base
{
    /**
     * Path to send uploads (must have write permissions)
     *
     * @var string
     */
    const UPLOAD_DIR = parent::UPLOAD_BASE_DIR . 'videos';
    
    /**
     * Table to save data
     *
     * @var string
     */
    public static $table = "videos";
    
    /**
     * Video ID from the source
     * 
     * @var string(64)
     */
    public $videoid;
    
    /**
     * Where this video came from?
     * 
     * @var string(24)
     * @see Video::$videoSources
     */
    public $videosource;
    
    /**
     * Video title
     * 
     * @var string(255)
     */
    public $title;
    
    /**
     * Video description
     * 
     * @var string
     */
    public $description;
    
    /**
     * Date that the video was imported
     * 
     * @var string|datetime
     */
    public $date;
    
    /**
     * Video image filename
     * 
     * @var string
     */
    public $image;
    
    /**
     * Internal video ID
     * 
     * @var int
     */
    public $id;
    
    /**
     * Available sources to import video
     * 
     * @var string
     */
    public static $videoSources = array(
        'YouTube' => 'youtube'
    );

    /**
     * Create or update a new video
     *
     * @return PDOStatement
     */
    public function save()
    {
        $data = $this->prepare();
        if (isset($data['valid']) && ! $data['valid']){
            return $data;
        } else {
            try {
                if ($this->id) {
                    $video = $this->getByColumn('id', $this->id)[0];
                    $this->image = $video->image;
                    $query = $this->update(self::$table)
                        ->set($data)
                        ->where("id = '{$this->id}'")
                        ->execute();
                } else {
                    $query = $this->insert($data)
                        ->into(self::$table)
                        ->execute();
                    
                    $this->id = $query;
                }

                return array(
                    'result' => (isset($video) ? 'Video updated!' : 'Video created!')
                );
            } catch (\Exception $e) {
                return array(
                    'result' => 'Failed to ' . (isset($video) ? 'update' : 'create') . ' video!',
                    'errors' => array($e->getMessage())
                );
            }
        }
    }

    /**
     * Runs a SQL DELETE statement
     * 
     * @param int $id Video internal ID to remove
     * @return PDOStatement
     */
    public function remove($id)
    {
        $id = (int) $id;
        return $this->delete()
            ->from(self::$table)
            ->where("id = $id")
            ->execute();
    }
    
    /**
     * Return the full URL of the video
     * 
     * @return string|boolean
     */
    public function getFullUrl()
    {
        switch ($this->videosource) {
            case 'youtube':
                return "https://youtube.com.br/watch/?v={$this->videoid}";
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * Prepare data to create/update video
     *
     * @return multitype:string |multitype:\Fennec\Model\string \Fennec\Model\integer
     */
    private function prepare()
    {
        $errors = $this->validate();
        if (! $errors['valid']) {
            return $errors;
        }

        $this->title = filter_var($this->title, \FILTER_SANITIZE_STRING);
        $this->description = filter_var($this->description, \FILTER_SANITIZE_STRING);
        
        $videoInfo = self::parseVideoUrl($this->url);
        $this->videosource = $videoInfo['source'];
        $this->videoid = $videoInfo['id'];
        
        switch ($this->videosource) {
            case 'youtube':
                $this->image = "video-{$this->videoid}.png";
                $ch = curl_init("http://img.youtube.com/vi/{$this->videoid}/0.jpg");
                curl_setopt($ch, \CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, \CURLOPT_RETURNTRANSFER, 1);
                $image = curl_exec($ch);
                $image = ImageWorkshop::initFromString($image);
                $image->save(self::UPLOAD_DIR, $this->image, true);
                break;
        }
        
        $result = array(
            'title' => $this->title,
            'videoid' => $this->videoid,
            'videosource' => $this->videosource,
            'description' => $this->description,
            'image' => $this->image
        );
        
        return $result;
    }

    /**
     * Validate post data
     *
     * @return multitype:string
     */
    private function validate()
    {
        $validation = array(
            'valid' => true,
            'errors' => array()
        );

        if (! $this->title) {
            $validation['valid'] = false;
            $validation['errors']['title'] = "Title is a required field";
        }
        
        if (! $this->url) {
            $validation['valid'] = false;
            $validation['errors']['url'] = "Video URL is a required field";
        } elseif ($this->url && ! self::parseVideoUrl($this->url)) {
            $validation['valid'] = false;
            $validation['errors']['url'] = "System cannot parse the video URL. Supported video sites:<br>";
            foreach (self::$videoSources as $name => $preg) {
                $validation['errors']['url'] .= $name . '<br>';
            }
        }

        return $validation;
    }
    
    /**
     * Return source and external ID from a video, if it source is compatible
     * 
     * @param string $url
     */
    protected static function parseVideoUrl($url)
    {
        $url = parse_url($url);
        $host = $url['host'];
        $query = $url['query'];
        $source = null;
        
        $result = array(
            'source' => null,
            'id' => null
        );
        
        foreach (self::$videoSources as $name => $preg) {
            if (preg_match('/' . $preg . '/is', $host, $source)) {
                $result['source'] = $source[0];
                break;
            }
        }

        switch ($result['source']) {
            case 'youtube':
                $id = str_replace('v=', '', $query);
                if (strpos($id, '&')) {
                    $id = substr($id, 0, strpos($id, '&'));
                }
                $result['id'] = $id;
                break;
        }
        
        if (! $result['source'] || ! $result['id']) {
            return false;
        } else {
            return $result;
        }
    }
}
