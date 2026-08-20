<?php

namespace App\Extensions\AzureOpenai\System\Http\Controllers\Settings;

use App\Domains\Entity\Models\Entity;
use App\Helpers\Classes\Helper;
use App\Http\Controllers\Controller;
use App\Models\Finance\AiChatModelPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AzureOpenaiSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('azure-openai::settings.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'azure_domain'          => 'required|string',
            'deployed_models'       => 'required|string',
            'azure_api_key'         => 'required|string',
            'azure_api_version'     => 'required|string',
            'selected_title.*'      => 'required',
            'selected_plans.*'      => 'sometimes',
            'no_plan_users.*'       => 'sometimes',
        ]);

        if (Helper::appIsNotDemo()) {
            setting([
                'azure_domain'      => $validated['azure_domain'],
                'deployed_models'   => $validated['deployed_models'],
                'azure_api_key'     => $validated['azure_api_key'],
                'azure_api_version' => $validated['azure_api_version'],
            ])->save();

            foreach ($validated['selected_title'] ?? [] as $key => $value) {
                Entity::query()
                    ->where('id', $key)
                    ->update([
                        'selected_title' => $value,
                    ]);

                Cache::forget('entities');
            }

            foreach ($validated['selected_plans'] ?? [] as $id => $value) {
                foreach ($value as $item) {
                    AiChatModelPlan::query()
                        ->create([
                            'plan_id'     => $item,
                            'entity_id'   => $id,
                        ]);
                }
            }

            foreach ($validated['no_plan_users'] ?? [] as $key => $value) {
                Entity::query()
                    ->where('id', $key)
                    ->update([
                        'is_selected' => true,
                    ]);
            }
        }

        return redirect()->back()->with(['message' => __('Azure OpenAI configured successfully!'), 'type' => 'success']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
