<?php
namespace App\Http\Traits;
use Intervention\Image\Facades\Image;

trait imageTraits{


    public function getImageUrl($file,$path){
            $file_path = null;
            if ($file && $file !== 'null') {
                $file_name = date('Ymd-his') . '.' . $file->getClientOriginalName();
                $destinationPath = $path . $file_name;
                $image = Image::make($file->getRealPath())->resize(400, 300);
                $image->save(public_path($destinationPath));
                $file_path = $destinationPath;
            }
            return $file_path ?? null;

    }


}

?>
