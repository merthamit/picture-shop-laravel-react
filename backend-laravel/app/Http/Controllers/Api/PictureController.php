<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImageRequest;
use App\Http\Resources\PictureResource;
use App\Http\Resources\UserResource;
use App\Models\Category;
use App\Models\Picture;
use App\Models\User;
use Illuminate\Http\Request;

class PictureController extends Controller
{
    //
    public function index()
    {
        return PictureResource::collection(Picture::whereStatus(1)->latest()->get());
    }

    // Fetch pictures by Id
    public function fetchById(Picture $picture)
    {
        if (!$picture->status) {
            abort(404);
        }
        return PictureResource::make($picture);
    }
    public function fetchByCategory(Category $category)
    {
        $pictures = $category->pictures()->where('status', 1)->latest()->get();
        return PictureResource::collection($pictures);
    }
    public function fetchByExt($ext)
    {
        $pictures = Picture::where('ext', $ext)->where('status', 1)->latest()->get();
        return PictureResource::collection($pictures);
    }
    public function fetchByTerm(Request $request)
    {
        $searchTerm = $request->searchTerm;
        $pictures = Picture::where('title', 'like', '%' . $searchTerm . '%')->latest()->get();
        return PictureResource::collection($pictures);
    }
    public function fetchExtentions()
    {
        $extensions = Picture::select('ext')->distinct()->get();
        return response()->json($extensions);
    }
    public function uploadFile(StoreImageRequest $request)
    {
        $user = User::find($request->user_id);
        $file = $request->file('file');
        $file_name = $this->saveImage($file);
        Picture::create([
            'title' => $request->title,
            'price' => $request->price,
            'user_id' => $user->id,
            'category_id' => $request->category_id,
            'file_path' => 'storage/user/images/' . $file_name,
            'ext' => $file->getClientOriginalExtension(),
        ]);

        return response([
            'message' => 'Picture has been saved successfully.',
            'user' => UserResource::make($user),
        ]);
    }

    public function saveImage($file)
    {
        $file_name = time() . '_' . 'picture' . '_' . $file->getClientOriginalName();
        $file->storeAs('user/images', $file_name, 'public');
        return $file_name;
    }
    public function downloadPicture(Picture $picture)
    {
        return response()->download($picture->file_path);
    }
}
