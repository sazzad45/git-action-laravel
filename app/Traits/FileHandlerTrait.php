<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

trait FileHandlerTrait
{
    /**
     * Process User Image
     *
     * @param Request $request
     * @return null|string
     */
    protected function processPhoto(Request $request)
    {
        try {

            $fileName = null;

            if($request->hasFile('photo'))
            {
                $str = Str::random(5);
                $fileName = uniqid().$str.time().'.'.$request->file('photo')->getClientOriginalExtension();
                $path = '/version20/users/photos/';

                if(! $this->isReallyImage($request->file('photo')->getClientOriginalExtension())) {
                    throw new \Exception("NOT AN IMAGE!");
                }

                $image_normal = Image::make( $request->file('photo') );
                $image_thumb = Image::make($image_normal)->resize(200, 200, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                $image_thumb = $image_thumb->stream();

                Storage::disk(config('app.filesfrom'))->put($path.$fileName, file_get_contents($request->file('photo')));
                Storage::disk(config('app.filesfrom'))->put($path.'thumbnails/'.$fileName, $image_thumb->__toString());

                return config('app.assetcdn').$path.'thumbnails/'.$fileName;
            }

            return null;

        }catch(\Exception $e) {
            Log::error($e->getFile(). ' '. $e->getLine(). ' '. $e->getMessage());
            return null;
        }
    }

    /**
     * Process Verification Doc
     *
     * @param Request $request
     * @return string
     */
    protected function processVerificationDoc(Request $request)
    {
        try {
            $fileName = null;

            if($request->hasFile('document_copy'))
            {
                $str = Str::random(5);
                $fileName = uniqid().$str.time().'.'.$request->file('document_copy')->getClientOriginalExtension();
                $path = '/version20/users/docs/';

                if(! $this->isReallyImage($request->file('document_copy')->getClientOriginalExtension())) {
                    throw new \Exception("NOT AN IMAGE!");
                }

                Storage::disk(config('app.filesfrom'))
                        ->put($path.$fileName, file_get_contents($request->file('document_copy')));

                return config('app.assetcdn').$path.$fileName;
            }

        }catch(\Exception $e) {
            Log::error($e->getFile(). ' '. $e->getLine(). ' '. $e->getMessage());
            return null;
        }
    }

    protected function uploadCustomerVerificationDocFile(Request $request, $file)
    {
        try {
            $fileName = null;

            if ($request->hasFile($file)) {
                $str = Str::random(5);
                $fileName = uniqid() . $str . time() . '.' . $request->file($file)->getClientOriginalExtension();
                $path = '/version20/agent/customer/docs/';

                if (!$this->isReallyImage($request->file($file)->getClientOriginalExtension())) {
                    throw new \Exception("NOT AN IMAGE!");
                }

                Storage::disk(config('app.filesfrom'))
                    ->put($path . $fileName, file_get_contents($request->file($file)));

                return config('app.assetcdn') . $path . $fileName;
            }

            return null;
        } catch (\Exception $e) {
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            return null;
        }
    }

    protected function isReallyImage($extension)
    {
        if(in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
            return true;
        }

        return false;
    }
}
