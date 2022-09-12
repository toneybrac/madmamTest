<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
{
    
    public function __construct()
    {
       $this->middleware('auth')->except(['index', 'show']);
    }

    public function index(Request $request){
        if($request->search){
            $posts = Post::where('title', 'like', '%' . $request->search . '%')
            ->orWhere('body', 'like', '%' . $request->search . '%')->latest()->paginate(4);
        } elseif($request->category) {
             $posts = Category::where('name', $request->category)->firstorfail()->posts()->paginate(3)->withQueryString();
        }
        else{
            $posts = Post::latest()->paginate(4);
        }

        
        $categories = Category::all();
        return view('blogPosts.blog', compact('posts', 'categories'));
    }

    public function create() {
        $categories = Category::all();
        return view('blogPosts.create-blog-post', compact('categories'));
    }

    public function store(Request $request) {

       $request->validate([
           'title' => 'required',
           'image' => 'required | image',
           'body' => 'required',
           'category_id' => 'required'
       ]);  

       $title = $request->input('title');
       $category_id = $request->input('category_id');
       
       if(Post::latest()->first() !== null ) {
        $postId = Post::latest()->first()->id + 1;
       }else {
        $postId = 1;
       }

       $slug = Str::slug($title, '-'). '-' . $postId;
       $user_id = Auth::user()->id;
       $body = $request->input('body');

      //File upload
      $imagePath = 'storage/' . $request->file('image')->store('postsImages', 'public');
       
      // 'storage'.  postsImages/Rhxf3o79XCWwrSlokJ2MUpV06P9robY5cTTD2tj9.jpg
      

      $post = new Post();
      $post->title = $title;
      $post->category_id = $category_id;
      $post->slug = $slug;
      $post->user_id = $user_id;
      $post->body = $body;
      $post->imagePath = $imagePath;

      $post->save();

     return redirect()->back()->with('status', 'Post created successfully');

       //dd('validation passed, you can now request the input');
    }

    public function edit(Post $post) {
        if(auth()->user()->id === $post->user->id) {
            abort(403);
        }
        return view('blogPosts.edit-blog-post', compact('post'));
       }

    public function update(Request $request, Post $post) {
        $request->validate([
            'title' => 'required',
            'image' => 'required | image',
            'body' => 'required'
        ]);  
 
        $title = $request->input('title');
 
        $postId = $post->id;
        $slug = Str::slug($title, '-'). '-' . $postId;
        $user_id = Auth::user()->id;
        $body = $request->input('body');
 
       //File upload
       $imagePath = 'storage/' . $request->file('image')->store('postsImages', 'public');
        
       // 'storage'.  postsImages/Rhxf3o79XCWwrSlokJ2MUpV06P9robY5cTTD2tj9.jpg
       
 
       
       $post->title = $title;
       $post->slug = $slug;
       $post->body = $body;
       $post->imagePath = $imagePath;
 
       $post->save();
 
      return redirect()->back()->with('status', 'Post edited successfully');
 
        //dd('validation passed, you can now request the input');
       }

   // public function show($slug) {
    //    $post = Post::where('slug', $slug)->first();
    //    return view('blogPosts.single-blog-post', compact('post'));
   // }

   //Using route model binding
   public function show(Post $post) {
    $category = $post->category;

    $relatedPosts = $category->posts()->where('id', '!=', $post->id)->latest()->take(3)->get();
    return view('blogPosts.single-blog-post', compact('post', 'relatedPosts'));
   }

   public function destroy(Post $post) {
    $post->delete();
    return redirect()->back()->with('status', 'Post deleted successfully');
   }
}
