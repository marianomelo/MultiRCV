<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companies = Company::where('user_id', $request->user()->id)->get();

        return Inertia::render('Companies/Index', [
            'companies' => $companies
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Companies/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rut' => 'required|string|unique:companies,rut',
            'sii_password' => 'required|string',
        ]);

        $validated['user_id'] = $request->user()->id;

        $company = Company::create($validated);

        ActivityLog::log('create', 'Company', $company->id, "Empresa '{$company->name}' creada");

        return redirect()->route('companies.index')
            ->with('success', 'Empresa creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        return Inertia::render('Companies/Show', [
            'company' => $company
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        return Inertia::render('Companies/Edit', [
            'company' => $company
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rut' => 'required|string|unique:companies,rut,' . $company->id,
            'sii_password' => 'required|string',
        ]);

        $company->update($validated);

        ActivityLog::log('update', 'Company', $company->id, "Empresa '{$company->name}' actualizada");

        return redirect()->route('companies.index')
            ->with('success', 'Empresa actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        $companyName = $company->name;
        $companyId = $company->id;

        $company->delete();

        ActivityLog::log('delete', 'Company', $companyId, "Empresa '{$companyName}' eliminada");

        return redirect()->route('companies.index')
            ->with('success', 'Empresa eliminada exitosamente.');
    }
}
