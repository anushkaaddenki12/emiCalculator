<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoanDetail;
use Carbon\Carbon;
use DB;
use Schema;

class LoanDetailsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $loanDetails = LoanDetail::all();
        return view('loan_details.index', compact('loanDetails'));
    }

 
  public function process()
{
    // Retrieve loan details
    $loans = DB::table('loan_details')->get();

    // Calculate the min and max dates
    $minDate = DB::table('loan_details')->min('first_payment_date');
    $maxDate = DB::table('loan_details')->max('last_payment_date');

    // Convert to Carbon instances for easy date manipulation
    $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', $minDate)->startOfMonth();
    $endDate = \Carbon\Carbon::createFromFormat('Y-m-d', $maxDate)->endOfMonth();

    // Generate columns for each month in the range
    $columns = [];
    $currentDate = $startDate->copy();
    while ($currentDate->lessThanOrEqualTo($endDate)) {
        $columns[] = $currentDate->format('Y_M');
        $currentDate->addMonth();
    }

    // Check if emi_details table exists and drop it if it does
    if (Schema::hasTable('emi_details')) {
        Schema::drop('emi_details');
    }

    // SQL to create the emi_details table
    $columnsSql = implode(' DOUBLE DEFAULT 0, ', $columns) . ' DOUBLE DEFAULT 0';
    DB::statement("CREATE TABLE emi_details (clientid INT, $columnsSql)");

    // Process each loan and insert EMI details
    foreach ($loans as $loan) {
        $emiAmount = round($loan->loan_amount / $loan->num_of_payment, 2);
        $loanStartDate = \Carbon\Carbon::createFromFormat('Y-m-d', $loan->first_payment_date)->startOfMonth();
        $loanEndDate = \Carbon\Carbon::createFromFormat('Y-m-d', $loan->last_payment_date)->endOfMonth();

        $emiData = array_fill_keys($columns, 0);
        $emiCount = 0;

        foreach ($columns as $column) {
            $currentMonth = \Carbon\Carbon::createFromFormat('Y_M', $column);

            if ($currentMonth->between($loanStartDate, $loanEndDate)) {
                if ($emiCount < $loan->num_of_payment - 1) {
                    $emiData[$column] = $emiAmount;
                } else {
                    // Adjust the last EMI to ensure the total matches the loan amount
                    $emiData[$column] = $loan->loan_amount - ($emiAmount * ($loan->num_of_payment - 1));
                }
                $emiCount++;
            }
        }

        // Insert the EMI data into the emi_details table
        DB::table('emi_details')->insert(array_merge(['clientid' => $loan->clientid], $emiData));
    }

    return redirect()->back()->with('status', 'EMI details processed!');
}



}
