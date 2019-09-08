<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feed;
use Validator;
use Corcel\Model\Taxonomy;
use Corcel\Model\Post;

class FeedController extends Controller
{
    //
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_category()
    {
        $cats = [];
        $all_cats = Taxonomy::where('taxonomy', 'category')->get();
        foreach ($all_cats as $cat) {
            if ($cat->parent == '0') {
//                $catid = $cat->term_id;
                $catname = $cat->term;
                $cats[] = $catname;
            } else {
                continue;
//                $catid = $cat->parent;
//                $catname = $cat->term->name;
//                echo $catid.$catname." NOT A MOTHER CAT<br>";
            }
        }
        return $cats;
    }

    public function create_uuid()
    {
        $str = md5(uniqid(mt_rand(), true));
        $UUID = substr($str, 0, 8) . '-';
        $UUID .= substr($str, 8, 4) . '-';
        $UUID .= substr($str, 12, 4) . '-';
        $UUID .= substr($str, 16, 4) . '-';
        $UUID .= substr($str, 20, 12);

        $feeds = Feed::where(['uuid' => $UUID])->first();
        if (!$feeds) {
            return strtoupper($UUID);
        } else {
            return $this->create_uuid();
        }
    }

    public function index(Request $request)
    {
        $cats = $this->get_category();
        //$feeds = Feed::orderBy('id', 'asc')->paginate(10);
        //if(isset($request->pagination)){
        //    $feeds = Feed::orderBy('id', 'asc')->paginate($request->pagination);
        //}else{
        //    $feeds = Feed::orderBy('id', 'asc')->paginate(10);
        //}
        //return view('feeds.index',compact('feeds', 'cats'))
        //    ->with('i', (request()->input('page', 1) - 1) * 10);
        $feeds = Feed::all();
        return view('feeds.home', compact('feeds', 'cats'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $cats = $this->get_category();
        return view('feeds.create', compact('cats'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /**
         * request()->validate([
         *     'title' => 'required',
         * ]);
         */
        //dd($request->all());
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'category' => 'required_without_all',
        ], [
            'title.required' => '請填寫名稱',
            'category.required_without_all' => '請選擇至少一個分類',
        ]);

        if ($validator->fails()) {
            return redirect()->route('feeds.create')
                ->with(['title' => $request->title])
                ->withErrors($validator);
        }

        if ($request->uuid) {
            $feeds = Feed::where(['uuid' => $request->uuid])->first();
            if ($feeds) {
                return redirect()->route('feeds.create')
                    ->with(['title' => $request->title])
                    ->withErrors('重複的UUID');
            }
            $uuid_param = $request->uuid;
        } else {
            $uuid_param = $this->create_uuid();
        }

        $cat_param = null;
        foreach ($request->category as $cat) {
            $cat_param .= $cat . ',';
        }
        $request->merge([
            'category' => $cat_param,
            'uuid' => $uuid_param,
        ]);
        //dd($request->all());
        $feed = Feed::create($request->all());
        return redirect()->route('feeds.index')
            ->with('success', $feed->title . ' 已建立');
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $feed = Feed::find($id);
        return view('feeds.show', compact('feed'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $cats = $this->get_category();
        $feed = Feed::find($id);
        $cat_params = explode(",", $feed->category, -1);
        return view('feeds.edit', compact('feed', 'cats', 'cat_params'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'category' => 'required_without_all',
        ], [
            'title.required' => '請填寫名稱',
            'category.required_without_all' => '請選擇至少一個分類',
        ]);

        if ($validator->fails()) {
            return back()->withInput()
                ->with(['title' => $request->title])
                ->withErrors($validator);
        }
        $cat_param = null;
        foreach ($request->category as $cat) {
            $cat_param .= $cat . ',';
        }
        $request->merge([
            'category' => $cat_param,
        ]);
        Feed::find($id)->update($request->all());
        return redirect()->route('feeds.index')
            ->with('success', '編號 ' . $id . ' 修改完成');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Feed::find($id)->delete();
        return redirect()->route('feeds.index')
            ->with('success', '編號 ' . $id . ' 已刪除');
    }
}
