<?php

namespace App\Http\Controllers;

use App\Astronomer;
use Illuminate\Http\Request;
use App\Publication;
use Illuminate\Support\Facades\DB;
use Session;

class PubController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pub.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate($request, [
            'username' => 'required|userIsRF',
            'date_of_publication' => 'required|before_or_equal:now',
            'doi' => 'required|min:0|unique:Publications,doi',
        ]);


        $pub = new Publication;


        $pub->date_of_publication = $request->date_of_publication;
        $pub->doi = $request->doi;

        $pub->save();

        $id = DB::table('astronomers')->where('username',$request->username)
            ->first()->id;

        DB::table('pub_rf')->insert(['pub_id'=>$pub->id, 'rf_id'=>$id]);

        return redirect()->route('pub.show', $pub->id);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pub= Publication::find($id);
        if(!is_null($pub)){
            $pubs = DB::table('pub_rf')
                ->where('pub_id',$pub->id)
                ->pluck('rf_id')->toArray();

            $astronomers = DB::table('astronomers')->whereIn('id',$pubs)
                ->get();
            return view('pub.show')->withPub($pub)->withAstronomers($astronomers);
        } else{
            return null;
        }
    }

    /**
     * Show the form for added an author.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function author($id)
    {
        $pub= Publication::find($id);
        if(!is_null($pub)){
            $pubs = DB::table('pub_rf')->where('pub_id',$pub->id)
                ->pluck('rf_id')->toArray();
            $astronomers = DB::table('astronomers')->whereIn('id',$pubs)
                ->get();
            return view('pub.author')->withPub($pub)->withAstronomers($astronomers);
        } else{
            return null;
        }
    }


    public function showReferencePage($id)
    {
        $pub = Publication::find($id);
        if(!is_null($pub)){
            $pub_ids = DB::table('publication_references')
                ->where('referrer_id', $pub->id)
                ->pluck('reference_id')->toArray();
            $pubs = DB::table('publications')->whereIn('id',$pub_ids)
                ->get();
            return view('pub.showReferencePage')->withPub($pub)->withPubs($pubs);
        } else {
            return null;
        }
    }

    /**
     * Adds a new entry in the pivot table for
     * the publication-publication relation. Adds a new
     * publication
     * @param Request $request
     * @return some view
     */
    public function reference(Request $request)
    {
        $this->validate($request, [
            'referrer_id' => 'required|exists:publications,id',
            'doi' => 'required|min:0|exists:publications,doi',
        ]);

        $reference_id = DB::table('publications')->where('doi',$request->doi)
            ->first()->id;

        $count = DB::table('publication_references')
            ->where('referrer_id',$request->referrer_id)
            ->where('reference_id',$reference_id)
            ->count();

        if($count > 0){
            Session::flash('error', 'Error, this reference already exists');
            return redirect()->route('pub.show', $request->referrer_id);
        }
        else{
            DB::table('publication_references')
                ->insert(['referrer_id' => $request->referrer_id, 'reference_id' => $reference_id]);
        }
        Session::flash('success', 'Reference was added.');
        return redirect()->route('pub.show', $request->referrer_id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateAuthor(Request $request, $id)
    {
        $this->validate($request, [
            'username' => 'required|userIsRF'
        ]);

        $pub = Publication::find($id);

        $astronomer = DB::table('astronomers')->where('username',$request->username)
            ->first()->id;

        $count = DB::table('pub_rf')
            ->where('pub_id',$id)
            ->where('rf_id',$astronomer)
            ->count();

        if($count > 0){
            Session::flash('error', 'Error, this combination exists already');
            return redirect()->route('pub.show', $pub->id);
        }
        else{
            DB::table('pub_rf')
                ->insert(['pub_id' => $id, 'rf_id' => $astronomer]);
        }

        Session::flash('success', 'Author was added.');
        return redirect()->route('pub.show', $pub->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $pub = Publication::find($id);
        $pub->delete();

        Session::flash('delete', 'Publication was deleted.');
        return redirect()->action('PagesController@getIndex');
    }

    /**
     * Searches by  DOI
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function searchByDOI(Request $request){

        $this->validate($request, [
            'name' => 'required|exists:publications,doi'
        ]);

        $pubs = DB::table('publications')->where('doi',$request->name)
            ->get();

        return view('pub.output')->withPubs($pubs);
    }

    public function paper(Request $request)
    {
        $this->validate($request, [
            'minmax' => 'required|bet:0,1'
        ]);
        $pub = DB::table('publication_references')
                ->selectRaw('reference_id, count(*) as total')
                ->groupBy('reference_id')
                ->get();
        
        if($pub->isEmpty()){
            return view('pub.paper')->withPub($pub)->withSkree(2);
        }

        if($request->minmax == 1){
            $maximini = $pub->max('total');
        }else{
            $maximini = $pub->min('total');
        }
        $rID = $pub->where('total',$maximini)
            ->pluck('reference_id')
            ->first();
        $doi = DB::table('publications')->where('id',$rID)
                ->first()->doi;

        return view('pub.paper')->withDoi($doi)->withCount($maximini)->withSkree($request->minmax);
    }

    public function gross(Request $request)
    {
        $this->validate($request, [
            'minmax' => 'required|bet:0,1'
        ]);
        
        $pub = DB::table('publication_references')
                ->join('pub_rf', 'publication_references.reference_id', '=', 'pub_rf.pub_id')
                ->join('researcher_fellowships', 'pub_rf.rf_id','=','researcher_fellowships.id')
                ->join('institutions', 'researcher_fellowships.institution_id','=','institutions.id')
                ->selectRaw('institutions.name as name, count(*) as total')
                ->groupBy('institutions.name')
                ->get();
        
        if($pub->isEmpty()){
            return view('pub.insti')->withPub($pub)->withSkree(2);
        }

        if($request->minmax == 1){
            $maximini = $pub->max('total');
        }else{
            $maximini = $pub->min('total');
        }
        $rID = $pub->where('total',$maximini)
            ->pluck('name')
            ->first();

        return view('pub.insti')->withName($rID)->withCount($maximini)->withSkree($request->minmax)->withAll($pub);
    }

    public function ave(Request $request)
    {
        $this->validate($request, [
            'minmax' => 'required|bet:0,1'
        ]);
        
        $pub = DB::select('SELECT institutions.name, AVG(total) as average
                            FROM institutions
                            JOIN ( SELECT sub.rf_id as rf_id, researcher_fellowships.institution_id as insti_id, sub.total as total
                                    FROM researcher_fellowships
                                    JOIN( SELECT pub_rf.rf_id as rf_id, count(*) as total
                                        FROM publication_references
                                        JOIN(
                                            pub_rf
                                        )
                                        ON pub_rf.pub_id = publication_references.reference_id
                                        GROUP BY(pub_rf.rf_id)
                                    ) sub
                                    ON sub.rf_id = researcher_fellowships.id
                            )dre
                            ON institutions.id = dre.insti_id
                            GROUP BY(institutions.name)');
        $pub = collect($pub);
        if($pub->isEmpty()){
            return view('pub.insti')->withPub($pub)->withSkree(2);
        }

        if($request->minmax == 1){
            $maximini = $pub->max('average');
        }else{
            $maximini = $pub->min('average');
        }
        $rID = $pub->where('average',$maximini)
            ->pluck('name')
            ->first();

        return view('pub.ave')->withName($rID)->withCount($maximini)->withSkree($request->minmax)->withAll($pub);
    }
}
