<?php

namespace App\Exports;
use App\Models\Lead;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeadsExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function collection()
    {
        $data = Lead::where('created_by', \Auth::user()->ownerId())->get();

        foreach ($data as $k => $lead) {
            unset( $lead->sources, $lead->products, $lead->notes, $lead->labels, $lead->order, $lead->is_converted);
            $created_bys = User::find($lead->created_by);
            $created_by = $created_bys->name;
            $data[$k]['created_by'] = $created_by;
        }

        return $data;
    }

    public function headings(): array
    {

        return [
        "Id",
        "Name",
        "Email",
        "Subject",
        "User_id",
        "pipeline_id",
        "Stage_id",
        "Phone",
        "created_by",
        "is_active",
        "date",
        "created_at",
        "updated_at",


        ];
    }
}
