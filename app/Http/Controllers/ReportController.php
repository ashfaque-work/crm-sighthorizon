<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Source;
use App\Models\User;
use App\Models\Pipeline;
use App\Models\Deal;
use App\Models\UserDeal;
use App\Models\ClientDeal;
use App\Models\ExpenseCategory;
use App\Models\Expense;
use App\Models\Invoice;
use Carbon\Carbon;
use Nette\Schema\Expect;

class ReportController extends Controller
{
    //
    public function leadreport(Request $request)
    {
        $user      = \Auth::user();
        $leads = Lead::orderBy('id');
        $leads->where('created_by', \Auth::user()->creatorId());

        $user_week_lead = Lead::orderBy('created_at')->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->get()->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        });
        $carbaoDay = Carbon::now()->startOfWeek();

        $weeks = [];
        for ($i = 0; $i < 7; $i++) {
            $weeks[$carbaoDay->startOfWeek()->addDay($i)->format('Y-m-d')] = 0;
        }
        foreach ($user_week_lead as $name => $leads) {
            $weeks[$name] = $leads->count();
        }

        $devicearray          = [];
        $devicearray['label'] = [];
        $devicearray['data']  = [];

        foreach ($weeks as $name => $leads) {
            $devicearray['label'][] = Carbon::parse($name)->format('l');
            $devicearray['data'][] = $leads;
        }
        $leads = Lead::where('created_by', '=', \Auth::user()->ownerId())->get();

        $lead_source = Source::where('created_by', \Auth::user()->ownerId())->get();

        $leadsourceName = [];
        $leadsourceeData = [];
        foreach ($lead_source as $lead_source_data) {
            $lead_source = lead::where('created_by', \Auth::user()->id)->where('sources', $lead_source_data->id)->count();
            $leadsourceName[] = $lead_source_data->name;
            $leadsourceeData[] = $lead_source;
        }


        // monthly report

        $labels = [];
        $data   = [];


        if (!empty($request->start_month) && !empty($request->end_month)) {
            $start = strtotime($request->start_month);
            $end   = strtotime($request->end_month);
        } else {
            $start = strtotime(date('Y-01'));
            $end   = strtotime(date('Y-12'));
        }

        $leads = Lead::orderBy('id');
        $leads->where('date', '>=', date('Y-m-01', $start))->where('date', '<=', date('Y-m-t', $end));
        $leads->where('created_by', \Auth::user()->creatorId());
        $leads = $leads->get();

        $currentdate = $start;
        while ($currentdate <= $end) {
            $month = date('m', $currentdate);
            $year  = date('Y');

            if (!empty($request->start_month)) {
                $leadFilter = Lead::where('created_by', \Auth::user()->creatorId())->whereMonth('date', $request->start_month)->whereYear('date', $year)->get();

            } else {
                $leadFilter = Lead::where('created_by', \Auth::user()->creatorId())->whereMonth('date', $month)->whereYear('date', $year)->get();

            }

            $data[]      = count($leadFilter);
            $labels[]    = date('M Y', $currentdate);
            $currentdate = strtotime('+1 month', $currentdate);


            if (!empty($request->start_month)) {
                $cdate = '01-' . $request->start_month . '-' . $year;
                $mstart = strtotime($cdate);
                $labelss[]    = date('M Y', $mstart);

                return response()->json(['data' => $data, 'name' => $labelss]);
            }
        }

        if(empty($request->start_month) && !empty($request->all())){
            return response()->json(['data' => $data, 'name' => $labels]);
        }
        $filter['startDateRange'] = date('M-Y', $start);
        $filter['endDateRange']   = date('M-Y', $end);

        $monthList = $month = $this->yearMonth();

        //staff report
        $leads = Lead::where('created_by', '=', \Auth::user()->ownerId())->get();

        if ($request->type == "staff_repport") {
            $form_date = date('Y-m-d H:i:s', strtotime($request->From_Date));
            $to_date = date('Y-m-d H:i:s', strtotime($request->To_Date));

            if (!empty($request->From_Date) && !empty($request->To_Date)) {

                $lead_user = User::where('created_by', \Auth::user()->id)->where('type', 'Employee')
                ->get();
                $leaduserName = [];
                $leadusereData = [];
                foreach ($lead_user as $lead_user_data) {
                    $lead_user = Lead::where('created_by', \Auth::user()->id)->where('user_id', $lead_user_data->id)->whereBetween('created_at', [$form_date, $to_date])->count();
                    $leaduserName[] = $lead_user_data->name;
                    $leadusereData[] = $lead_user;
                }
                return response()->json(['data' => $leadusereData, 'name' => $leaduserName]);
            }
        } else {
            $lead_user = User::where('created_by', \Auth::user()->ownerId())->where('type', 'Employee')
            ->get();
            $leaduserName = [];
            $leadusereData = [];
            foreach ($lead_user as $lead_user_data) {
                $lead_user = Lead::where('created_by', \Auth::user()->id)->where('user_id', $lead_user_data->id)->count();
                $leaduserName[] = $lead_user_data->name;
                $leadusereData[] = $lead_user;
            }
        }

        $leads = Lead::where('created_by', '=', \Auth::user()->ownerId())->get();

        $lead_pipeline = Pipeline::where('created_by', \Auth::user()->ownerId())->get();

        $leadpipelineName = [];
        $leadpipelineeData = [];
        foreach ($lead_pipeline as $lead_pipeline_data) {
            $lead_pipeline = lead::where('created_by', \Auth::user()->id)->where('pipeline_id', $lead_pipeline_data->id)->count();
            $leadpipelineName[] = $lead_pipeline_data->name;
            $leadpipelineeData[] = $lead_pipeline;
        }


        return view('report.lead', compact('devicearray', 'leadsourceName', 'leadsourceeData', 'labels', 'data', 'filter', 'monthList','leads', 'leaduserName', 'leadusereData', 'user', 'leadpipelineName', 'leadpipelineeData'));
    }

    public function dealreport(Request $request)
    {
        $user      = \Auth::user();
        $deals = Deal::orderBy('id');
        $deals->where('created_by', \Auth::user()->creatorId());

        $user_week_deal = Deal::orderBy('created_at')->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->get()->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        });

        $carbaoDay = Carbon::now()->startOfWeek();
        $weeks = [];
        for ($i = 0; $i < 7; $i++) {
            $weeks[$carbaoDay->startOfWeek()->addDay($i)->format('Y-m-d')] = 0;
        }
        foreach ($user_week_deal as $name => $deals) {
            $weeks[$name] = $deals->count();
        }

        $devicearray          = [];
        $devicearray['label'] = [];
        $devicearray['data']  = [];
        foreach ($weeks as $name => $deals) {
            $devicearray['label'][] = Carbon::parse($name)->format('l');
            $devicearray['data'][] = $deals;
        }
        $deals = Deal::where('created_by', '=', \Auth::user()->ownerId())->get();

        $deals_source = Source::where('created_by', \Auth::user()->ownerId())->get();

        $dealsourceName = [];
        $dealsourceeData = [];
        foreach ($deals_source as $deals_source_data) {
            $deals_source = Deal::where('created_by', \Auth::user()->id)->where('sources', $deals_source_data->id)->count();
            $dealsourceName[] = $deals_source_data->name;
            $dealsourceeData[] = $deals_source;
        }
        if ($request->type == "deal_staff_repport") {
            $from_date = date('Y-m-d H:i:s', strtotime($request->From_Date));
            $to_date = date('Y-m-d H:i:s', strtotime($request->To_Date));

            if (!empty($request->From_Date) && !empty($request->To_Date)) {
                $user_deal = User::where('created_by', \Auth::user()->ownerId())->where('type', 'Employee')
                ->get();
                $dealUserData = [];
                $dealUserName = [];
                foreach ($user_deal as $user_deal_data) {

                    $user_deals = UserDeal::where('user_id', $user_deal_data->id)->whereBetween('created_at', [$from_date, $to_date])->count();
                    $dealUserName[] = $user_deal_data->name;
                    $dealUserData[] = $user_deals;
                }
                return response()->json(['data' => $dealUserData, 'name' => $dealUserName]);
            }
        } else {
            $user_deal = User::where('created_by', \Auth::user()->ownerId())->where('type', 'Employee')
                ->get();
            $dealUserData = [];
            $dealUserName = [];
            foreach ($user_deal as $user_deal_data) {
                $user_deals = UserDeal::where('user_id', $user_deal_data->id)->count();

                $dealUserName[] = $user_deal_data->name;
                $dealUserData[] = $user_deals;
            }
        }

        $deals = Deal::where('created_by', '=', \Auth::user()->ownerId())->get();

        $deal_pipeline = Pipeline::where('created_by', \Auth::user()->ownerId())->get();

        $dealpipelineName = [];
        $dealpipelineeData = [];
        foreach ($deal_pipeline as $deal_pipeline_data) {
            $deal_pipeline = Deal::where('created_by', \Auth::user()->id)->where('pipeline_id', $deal_pipeline_data->id)->count();
            $dealpipelineName[] = $deal_pipeline_data->name;
            $dealpipelineeData[] = $deal_pipeline;
        }

        if ($request->type == "client_repport") {

            $from_date1 = date('Y-m-d H:i:s', strtotime($request->from_date));
            $to_date1 = date('Y-m-d H:i:s', strtotime($request->to_date));
            if (!empty($request->from_date) && !empty($request->to_date)) {
                $client_deal = User::where('created_by', \Auth::user()->ownerId())
                ->where('type', 'Client')
                ->get();
                $dealClientData = [];
                $dealClientName = [];
                foreach ($client_deal as $client_deal_data) {

                    $deals_client = ClientDeal::where('client_id', $client_deal_data->id)->whereBetween('created_at', [$from_date1, $to_date1])->count();
                    $dealClientName[] = $client_deal_data->name;
                    $dealClientData[] = $deals_client;
                }
                return response()->json(['data' => $dealClientData, 'name' =>  $dealClientName]);
            }
        } else {
            $client_deal = User::where('created_by', \Auth::user()->ownerId())
            ->where('type', 'Client')
            ->get();
            $dealClientName = [];
            $dealClientData = [];
            foreach ($client_deal as $client_deal_data) {
                $deals_client = ClientDeal::where('client_id', $client_deal_data->id)->count();
                $dealClientName[] = $client_deal_data->name;
                $dealClientData[] = $deals_client;
            }
        }
        $labels = [];
        $data   = [];

        if (!empty($request->start_month) && !empty($request->end_month)) {
            $start = strtotime($request->start_month);
            $end   = strtotime($request->end_month);
        } else {
            $start = strtotime(date('Y-01'));
            $end   = strtotime(date('Y-12'));
        }

        $deals = Deal::orderBy('id');
        $deals->where('created_at', '>=', date('Y-m-01', $start))->where('created_at', '<=', date('Y-m-t', $end));
        $deals->where('created_by', \Auth::user()->creatorId());
        $deals = $deals->get();

        $currentdate = $start;
        while ($currentdate <= $end) {
            $month = date('m', $currentdate);

            $year  = date('Y');

            if (!empty($request->start_month)) {
                $dealFilter = Deal::where('created_by', \Auth::user()->creatorId())->whereMonth('created_at', $request->start_month)->whereYear('created_at', $year)->get();
            } else {
                $dealFilter = Deal::where('created_by', \Auth::user()->creatorId())->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();
            }

            $data[]      = count($dealFilter);
            $labels[]    = date('M Y', $currentdate);
            $currentdate = strtotime('+1 month', $currentdate);

            if (!empty($request->start_month)) {
                $cdate = '01-' . $request->start_month . '-' . $year;
                $mstart = strtotime($cdate);
                $labelss[]    = date('M Y', $mstart);

                return response()->json(['data' => $data, 'name' => $labelss]);
            }
        }
        if(empty($request->start_month) && !empty($request->all())){
            return response()->json(['data' => $data, 'name' => $labels]);
        }
        $filter['startDateRange'] = date('M-Y', $start);
        $filter['endDateRange']   = date('M-Y', $end);

        $monthList = $month = $this->yearMonth();
        return view('report.deal', compact('devicearray', 'dealsourceName', 'dealsourceeData', 'dealUserData', 'dealUserName', 'dealpipelineName', 'dealpipelineeData', 'data', 'labels', 'dealClientName', 'dealClientData','monthList'));
    }

    public function invoicereport(Request $request)
    {

        if(\Auth::user()->can('Invoice Report'))
        {
            $filter['deal'] = __('All');
            $filter['status']   = __('All');

            $deal = Deal::where('created_by', '=', \Auth::user()->ownerId())->get()->pluck('name', 'id');;
            $deal->prepend('Select Deal','');

            $status = Invoice::$statues;
            $invoices = Invoice::selectRaw('invoices.*,MONTH(issue_date) as month,YEAR(issue_date) as year');

            if($request->status != '')
            {
                $invoices->where('status', $request->status);

                $filter['status'] = Invoice::$statues[$request->status];
            }
            else
            {
                $invoices->where('status', '!=', 0);
            }
            $invoices->where('created_by', '=', \Auth::user()->creatorId());

            if(!empty($request->start_month) && !empty($request->end_month))
            {
                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);
            }
            else
            {
                $start = strtotime(date('Y-01'));
                $end   = strtotime(date('Y-12'));
            }
            $invoices->where('issue_date', '>=', date('Y-m-01', $start))->where('issue_date', '<=', date('Y-m-t', $end));


            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);


            if(!empty($request->deal))
            {
                $invoices->where('deal_id', $request->deal);
                $cust = Deal::find($request->deal);

                $filter['deal'] = !empty($cust) ? $cust->name : '';
            }


            $invoices = $invoices->get();


            $totalInvoice      = 0;
            $totalDueInvoice   = 0;
            $invoiceTotalArray = [];
            foreach($invoices as $invoice)
            {
                $totalInvoice    += $invoice->getTotal();
                $totalDueInvoice += $invoice->getDue();

                $invoiceTotalArray[$invoice->month][] = $invoice->getTotal();
            }
            $totalPaidInvoice = $totalInvoice - $totalDueInvoice;

            for($i = 1; $i <= 12; $i++)
            {
                $invoiceTotal[] = array_key_exists($i, $invoiceTotalArray) ? array_sum($invoiceTotalArray[$i]) : 0;
            }

            $monthList = $month = $this->yearMonth();

            return view('report.invoice', compact('invoices','deal','status', 'totalInvoice', 'totalDueInvoice', 'totalPaidInvoice', 'invoiceTotal', 'monthList', 'filter'));

        }
        else{
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

    }

    public function expensesreport(Request $request)
    {
        if(\Auth::user()->type == 'Owner')
        {
            $filter['deal'] = __('All');
            $filter['category']   = __('All');
            $deal = Deal::where('created_by', '=', \Auth::user()->ownerId())->get()->pluck('name', 'id');
            $deal->prepend('Select Deal','');
            $category = ExpenseCategory::where('created_by', '=', \Auth::user()->ownerId())->get()->pluck('name', 'id');
            $category->prepend('Select Category','');

            $expenses = Expense::selectRaw('expenses.*,MONTH(date) as month,YEAR(date) as year');
            if($request->category != '')
            {
                $expenses->where('category_id', $request->category);
                $filter['category'] = $category[$request->category];
            }
            else
            {
                $expenses->where('category_id', '!=', 0);
            }
            $expenses->where('created_by', '=', \Auth::user()->creatorId());
            if(!empty($request->start_month) && !empty($request->end_month))
            {
                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);
            }
            else
            {
                $start = strtotime(date('Y-01'));
                $end   = strtotime(date('Y-12'));
            }
            $expenses->where('date', '>=', date('Y-m-01', $start))->where('date', '<=', date('Y-m-t', $end));
            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);
            if(!empty($request->deal))
            {
                $expenses->where('deal_id', $request->deal);
                $cust = Deal::find($request->deal);
                $filter['deal'] = !empty($cust) ? $cust->name : '';
            }
            $expenses = $expenses->get();

            $totalExpense      = 0;
            $expenseTotalArray = [];
            foreach($expenses as $expense)
            {
                $totalExpense     += $expense->amount;
                $expenseTotalArray[$expense->month][] = $expense->amount;
            }
            for($i = 1; $i <= 12; $i++)
            {
                $expenseTotal[] = array_key_exists($i, $expenseTotalArray) ? array_sum($expenseTotalArray[$i]) : 0;
            }
            $monthList = $month = $this->yearMonth();

            return view('report.expense', compact('expenses','deal','category', 'totalExpense','expenseTotal', 'monthList', 'filter'));
        }
        else{
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

    }
    public function incomeVsExpenseSummary(Request $request)
    {
        if (\Auth::user()->type == 'Owner') {

            $deal = Deal::where('created_by', '=', \Auth::user()->ownerId())->whereIn(
                'created_by', [
                          1,
                          2,
                      ]
            )->get()->pluck('name', 'id');
            $deal->prepend('Select Deal', '');

            $data['monthList']  = $this->yearMonth();
            $data['yearList']   = $this->yearList();

            $filter['deal'] = __('All');

            if (isset($request->year)) {
                $year = $request->year;
            } else {
                $year = date('Y');
            }
            $data['currentYear'] = $year;

            // ------------------------------TOTAL EXPENSE-----------------------------------------------------------
            $expensesData = Expense::selectRaw('sum(expenses.amount) as amount,MONTH(date) as month,YEAR(date) as year');
            $expensesData->where('expenses.created_by', '=', \Auth::user()->creatorId());
            $expensesData->whereRAW('YEAR(date) =?', [$year]);

            if(!empty($request->deal))
            {
                $expensesData->where('deal_id', '=', $request->deal);
                $cat                = Deal::find($request->deal);
                $filter['deal'] = !empty($cat) ? $cat->name : '';

            }

            $expensesData->groupBy('month', 'year');
            $expensesData = $expensesData->get();

            $expenseArr = [];
            foreach ($expensesData as $k => $expenseData) {
                $expenseArr[$expenseData->month] = $expenseData->amount;
            }

            // ------------------------------TOTAL INVOICE INCOME-----------------------------------------------------------
            $invoices = Invoice::selectRaw('invoices.*,MONTH(issue_date) as month,YEAR(issue_date) as year');
            $invoices->where('invoices.created_by', '=', \Auth::user()->creatorId());
            $invoices->whereRAW('YEAR(issue_date) =?', [$year]);

            if(!empty($request->deal))
            {
                $invoices->where('deal_id', '=', $request->deal);
            }
            $invoices = $invoices->get();

            $totalInvoice      = 0;
            $totalDueInvoice   = 0;
            $invoiceTotalArray = [];
            foreach ($invoices as $invoice) {
                $totalInvoice    += $invoice->getTotal();
                $totalDueInvoice += $invoice->getDue();

                $invoiceTotalArray[$invoice->month][] = $invoice->getTotal();
            }
            $totalPaidInvoice = $totalInvoice - $totalDueInvoice;


            for ($i = 1; $i <= 12; $i++) {
                $expenseTotal[] = array_key_exists($i, $expenseArr) ? $expenseArr[$i] : 0;
                $invoiceTotal[] = array_key_exists($i, $invoiceTotalArray) ? array_sum($invoiceTotalArray[$i]) : 0;
            }

            $totalExpense = array_map(
                function () {
                    return array_sum(func_get_args());
                },
                $expenseTotal
            );

            $totalIncome = array_map(
                function(){
                    return array_sum(func_get_args());
                },
                $invoiceTotal
            );

            $profit = [];
            $keys   = array_keys($totalIncome + $totalExpense);
            foreach($keys as $v)
            {
                $profit[$v] = (empty($totalIncome[$v]) ? 0 : $totalIncome[$v]) - (empty($totalExpense[$v]) ? 0 : $totalExpense[$v]);
            }

            $data['expenseTotal'] = $expenseTotal;
            $data['invoiceTotal']  = $invoiceTotal;
            $data['deal']              = $deal;
            $data['profit']              = $profit;

            $filter['startDateRange'] = 'Jan-' . $year;
            $filter['endDateRange']   = 'Dec-' . $year;

            $monthList = $month = $this->yearMonth();
            $yearlist = $year = $this->yearList();


            return view('report.income_vs_expense', compact('filter','totalInvoice','totalDueInvoice' ,'totalPaidInvoice','monthList', 'yearlist','totalExpense','totalIncome'), $data);
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function yearMonth()
    {
        $month[] = __('January');
        $month[] = __('February');
        $month[] = __('March');
        $month[] = __('April');
        $month[] = __('May');
        $month[] = __('June');
        $month[] = __('July');
        $month[] = __('August');
        $month[] = __('September');
        $month[] = __('October');
        $month[] = __('November');
        $month[] = __('December');
        return $month;
    }
    public function yearList()
    {
        $starting_year = date('Y', strtotime('-5 year'));
        $ending_year   = date('Y');

        foreach (range($ending_year, $starting_year) as $year) {
            $years[$year] = $year;
        }

        return $years;
    }
}
