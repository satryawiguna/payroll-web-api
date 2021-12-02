<?php

use App\Core\Auth\AuthController;
use App\Modules\CompensationAdmin\Controllers\ElementClassificationController;
use App\Modules\CompensationAdmin\Controllers\ElementLinkController;
use App\Modules\CompensationAdmin\Controllers\PayrollBalanceController;
use App\Modules\CompensationAdmin\Controllers\PayrollElementController;
use App\Modules\CompensationAdmin\Controllers\PayrollFormulaController;
use App\Modules\CompensationAdmin\Controllers\PayrollGroupController;
use App\Modules\CompensationAdmin\Controllers\SalaryBasisController;
use App\Modules\MasterData\Controllers\FormulaListController;
use App\Modules\MasterData\Controllers\TrackingHistoryController;
use App\Modules\Payroll\Controllers\PayrollEntryController;
use App\Modules\Payroll\Controllers\PayrollProcessController;
use App\Modules\Payroll\Controllers\ReportPayrollController;
use App\Modules\Personal\Controllers\EmployeeController;
use App\Modules\WorkStructure\Controllers\DepartmentController;
use App\Modules\WorkStructure\Controllers\GradeController;
use App\Modules\WorkStructure\Controllers\LocationController;
use App\Modules\WorkStructure\Controllers\OfficeController;
use App\Modules\WorkStructure\Controllers\PositionController;
use App\Modules\WorkStructure\Controllers\ProjectController;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function(Router $router) {
    $router->post('login', [AuthController::class, 'login']);
    $router->post('logout', [AuthController::class, 'logout']);
    $router->post('refresh', [AuthController::class, 'refresh']);
    $router->post('me', [AuthController::class, 'me']);
});

Route::group(['middleware' => 'auth:api', 'prefix' => 'v1'], function(Router $router) {

    // <editor-fold desc="=== Master ===">

    $router->get('formula-list/list-cbx', [FormulaListController::class, 'listCbx']);
    $router->get('tracking-history/{name}/{id}', [TrackingHistoryController::class, 'list']);

    // </editor-fold>

    // <editor-fold desc="=== Work Structure ===">

    $router->get('offices/list-cbx', [OfficeController::class, 'listCbx']);
    $router->get('locations/list-cbx', [LocationController::class, 'listCbx']);
    $router->get('departments/list-cbx', [DepartmentController::class, 'listCbx']);
    $router->get('projects/list-cbx', [ProjectController::class, 'listCbx']);
    $router->get('positions/list-cbx', [PositionController::class, 'listCbx']);
    $router->get('grades/list-cbx', [GradeController::class, 'listCbx']);

    // </editor-fold>

    // <editor-fold desc="=== Personal Data ===">

    $router->get('employees/list-cbx', [EmployeeController::class, 'listCbx']);

    // </editor-fold>

    // <editor-fold desc="=== Compensation Administration ===">

    // == Element Classification ==
    $router->get('element-classifications', [ElementClassificationController::class, 'getPage']);
    $router->get('element-classifications/list-cbx', [ElementClassificationController::class, 'listCbx']);
    $router->get('element-classifications/{classificationId}', [ElementClassificationController::class, 'getOne']);
    $router->post('element-classifications', [ElementClassificationController::class, 'insert']);
    $router->put('element-classifications/{classificationId}', [ElementClassificationController::class, 'update']);
    $router->delete('element-classifications/{classificationId}', [ElementClassificationController::class, 'delete']);

    // == Payroll Group ==
    $router->get('payroll-groups', [PayrollGroupController::class, 'getPage']);
    $router->get('payroll-groups/list-cbx', [PayrollGroupController::class, 'listCbx']);
    $router->get('payroll-groups/{payGroupId}', [PayrollGroupController::class, 'getOne']);
    $router->post('payroll-groups', [PayrollGroupController::class, 'insert']);
    $router->put('payroll-groups/{payGroupId}', [PayrollGroupController::class, 'update']);
    $router->delete('payroll-groups/{payGroupId}', [PayrollGroupController::class, 'delete']);

    // == Salary Basis ==
    $router->get('salary-basis', [SalaryBasisController::class, 'getPage']);
    $router->get('salary-basis/{salaryBasisId}', [SalaryBasisController::class, 'getOne']);
    $router->post('salary-basis', [SalaryBasisController::class, 'insert']);
    $router->put('salary-basis/{salaryBasisId}', [SalaryBasisController::class, 'update']);
    $router->delete('salary-basis/{salaryBasisId}', [SalaryBasisController::class, 'delete']);

    // == Payroll Element ==
    $router->get('payroll-elements', [PayrollElementController::class, 'getPage']);
    $router->get('payroll-elements/list-cbx', [PayrollElementController::class, 'listCbx']);
    $router->get('payroll-elements/{elementId}', [PayrollElementController::class, 'getOne']);
    $router->post('payroll-elements', [PayrollElementController::class, 'insert']);
    $router->put('payroll-elements/{elementId}', [PayrollElementController::class, 'update']);
    $router->delete('payroll-elements/{elementId}', [PayrollElementController::class, 'delete']);

    $router->get('payroll-elements/values/{inputValueId}', [PayrollElementController::class, 'getOneInputValue']);
    $router->post('payroll-elements/{elementId}/values', [PayrollElementController::class, 'insertInputValue']);
    $router->put('payroll-elements/values/{inputValueId}', [PayrollElementController::class, 'updateInputValue']);
    $router->delete('payroll-elements/values/{inputValueId}', [PayrollElementController::class, 'deleteInputValue']);

    // == Payroll Formula ==
    $router->get('payroll-formulas', [PayrollFormulaController::class, 'getPage']);
    $router->get('payroll-formulas/list-cbx', [PayrollFormulaController::class, 'listCbx']);
    $router->get('payroll-formulas/{formulaId}', [PayrollFormulaController::class, 'getOne']);
    $router->post('payroll-formulas', [PayrollFormulaController::class, 'insert']);
    $router->put('payroll-formulas/{formulaId}', [PayrollFormulaController::class, 'update']);
    $router->delete('payroll-formulas/{formulaId}', [PayrollFormulaController::class, 'delete']);

    $router->get('payroll-formulas/results/{resultId}', [PayrollFormulaController::class, 'getOneFormulaResult']);
    $router->post('payroll-formulas/{formulaId}/results', [PayrollFormulaController::class, 'insertFormulaResult']);
    $router->put('payroll-formulas/results/{resultId}', [PayrollFormulaController::class, 'updateFormulaResult']);
    $router->delete('payroll-formulas/results/{resultId}', [PayrollFormulaController::class, 'deleteFormulaResult']);

    // == Element Link ==
    $router->get('element-links', [ElementLinkController::class, 'getPage']);
    $router->get('element-links/{linkId}', [ElementLinkController::class, 'getOne']);
    $router->post('element-links', [ElementLinkController::class, 'insert']);
    $router->put('element-links/{linkId}', [ElementLinkController::class, 'update']);
    $router->delete('element-links/{linkId}', [ElementLinkController::class, 'delete']);

    $router->get('element-links/values/{inputValueId}', [ElementLinkController::class, 'getOneValue']);
    $router->post('element-links/{linkId}/values', [ElementLinkController::class, 'insertValue']);
    $router->put('element-links/values/{inputValueId}', [ElementLinkController::class, 'updateValue']);
    $router->delete('element-links/values/{inputValueId}', [ElementLinkController::class, 'deleteValue']);

    // == Payroll Balance ==
    $router->get('payroll-balances', [PayrollBalanceController::class, 'getPage']);
    $router->get('payroll-balances/{balanceId}', [PayrollBalanceController::class, 'getOne']);
    $router->post('payroll-balances', [PayrollBalanceController::class, 'insert']);
    $router->put('payroll-balances/{balanceId}', [PayrollBalanceController::class, 'update']);
    $router->delete('payroll-balances/{balanceId}', [PayrollBalanceController::class, 'delete']);

    $router->get('payroll-balances/feeds/{feedId}', [PayrollBalanceController::class, 'getOneBalanceFeed']);
    $router->post('payroll-balances/{formulaId}/feeds', [PayrollBalanceController::class, 'insertBalanceFeed']);
    $router->put('payroll-balances/feeds/{feedId}', [PayrollBalanceController::class, 'updateBalanceFeed']);
    $router->delete('payroll-balances/feeds/{feedId}', [PayrollBalanceController::class, 'deleteBalanceFeed']);

    // </editor-fold>

    // <editor-fold desc="=== Payroll Process ===">

    // == Payroll Entry ==
    $router->get('payroll-entries/employees', [PayrollEntryController::class, 'getEmployees']);
    $router->get('payroll-entries/{entryId}', [PayrollEntryController::class, 'getOne']);
    $router->get('payroll-entries/employees/{employeeId}', [PayrollEntryController::class, 'getEmployee']);
    $router->get('payroll-entries/employees/{employeeId}/entries', [PayrollEntryController::class, 'getEntries']);
    $router->post('payroll-entries/employees/{employeeId}', [PayrollEntryController::class, 'insert']);
    $router->put('payroll-entries/{entryId}', [PayrollEntryController::class, 'update']);
    $router->delete('payroll-entries/{entryId}', [PayrollEntryController::class, 'delete']);
    $router->get('payroll-entries/values/{valueId}', [PayrollEntryController::class, 'getOneValue']);
    $router->put('payroll-entries/values/{valueId}', [PayrollEntryController::class, 'updateValue']);

    // == Payroll Process ==
    $router->get('payroll-process/new-process', [PayrollProcessController::class, 'getNewProcess']);
    $router->get('payroll-process/new-retro-pay', [PayrollProcessController::class, 'getNewRetroPay']);
    $router->post('payroll-process/new-process', [PayrollProcessController::class, 'insertNewProcess']);
    $router->post('payroll-process/new-retro-pay', [PayrollProcessController::class, 'insertNewRetroPay']);
    $router->post('payroll-process/{processId}/calculate', [PayrollProcessController::class, 'calculatePayroll']);
    $router->post('payroll-process/{processId}/validate', [PayrollProcessController::class, 'validateProcessed']);
    $router->delete('payroll-process/{processId}', [PayrollProcessController::class, 'deletePayroll']);
    $router->get('report/payslip/{employeeId}', [ReportPayrollController::class, 'perPayslip']);

    // </editor-fold>
});
