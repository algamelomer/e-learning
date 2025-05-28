<?php
namespace App\Trait;

use Illuminate\Http\Request;

trait FileUploadTrait
{
    function uploadImage(Request $request, $inputName, $path = '/images')
    {
        if ($request->hasFile($inputName)) {
            $image = $request->{$inputName};
            $ext = $image->getClientOriginalExtension();
            $imageName = 'img_' . uniqid() . '.' . $ext;

            $image->move(public_path($path), $imageName);

            return $path . '/' . $imageName;
        }
    }
}