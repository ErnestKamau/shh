@extends('layouts.ImaraAi.manager')

@section('kb-content')
<div class="manager-content">
    <div class="settings-header" style="margin-bottom: 32px;">
        <h1 style="font-size: 1.875rem; font-weight: 700; color: #111827; margin-bottom: 8px;">Model Configuration</h1>
        <p style="color: #6b7280; font-size: 1.05rem;">Fine-tune the technical behavior and performance of the underlying AI models.</p>
    </div>

    @if(session('success'))
        <div style="background: #ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 16px; border-radius: 12px; margin-bottom: 24px;">
            <i class="mdi mdi-check-circle" style="margin-right: 8px;"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: #fef2f2; border: 1px solid #ef4444; color: #991b1b; padding: 16px; border-radius: 12px; margin-bottom: 24px;">
            <i class="mdi mdi-alert-circle" style="margin-right: 8px;"></i>
            {{ session('error') }}
        </div>
    @endif

    <div style="width: 100%;">
        <div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
            <form action="{{ route('ai.settings.model-config.sync') }}" method="POST">
                @csrf
                <button type="submit" style="background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; padding: 8px 16px; border-radius: 8px; font-size: 0.875rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <i class="mdi mdi-sync"></i>
                    Sync with Local Ollama
                </button>
            </form>
        </div>

        <form action="{{ route('ai.settings.model-config.update') }}" method="POST">
            @csrf
            
            <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 24px;">
                <h2 style="font-size: 1.25rem; font-weight: 600; color: #111827; margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
                    <i class="mdi mdi-robot-mop-outline" style="color: #a72b2a;"></i>
                    General Inference Settings
                </h2>

                <div style="margin-bottom: 24px;">
                    <label for="default_model" style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px;">Default LLM Model</label>
                    <select name="default_model" id="default_model" 
                            style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem; color: #111827; background: #fff;">
                        @foreach($availableModels as $model)
                            <option value="{{ $model->model_name }}:{{ $model->version }}" 
                                {{ $settings['default_model'] == ($model->model_name . ':' . $model->version) ? 'selected' : '' }}>
                                {{ $model->model_name }} {{ $model->version != 'latest' ? '(v' . $model->version . ')' : '' }} - {{ $model->framework }}
                            </option>
                        @endforeach
                        {{-- Fallback if registry is empty --}}
                        @if($availableModels->isEmpty())
                            <option value="qwen2.5:3b" selected>Qwen 2.5 (3B) - Ollama (Default)</option>
                        @endif
                    </select>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; padding: 16px; background: #f9fafb; border-radius: 12px; margin-bottom: 24px;">
                    <div>
                        <div style="font-weight: 600; color: #374151;">Streaming Response</div>
                        <div style="font-size: 0.875rem; color: #6b7280;">Tokens are displayed in real-time as they are generated.</div>
                    </div>
                    <label class="switch" style="position: relative; display: inline-block; width: 44px; height: 24px;">
                        <input type="checkbox" name="streaming" value="1" {{ $settings['streaming'] ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;">
                        <span class="slider round" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px;"></span>
                    </label>
                </div>

                <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 32px 0;">

                <h2 style="font-size: 1.25rem; font-weight: 600; color: #111827; margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
                    <i class="mdi mdi-tune-vertical" style="color: #a72b2a;"></i>
                    Hyperparameters
                </h2>

                <div style="margin-bottom: 32px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <label for="temperature" style="font-weight: 600; color: #374151;">Temperature</label>
                        <span id="temp-val" style="background: rgba(167, 43, 42, 0.1); color: #a72b2a; padding: 2px 8px; border-radius: 6px; font-weight: 700; font-family: monospace;">{{ $settings['temperature'] }}</span>
                    </div>
                    <input type="range" name="temperature" id="temperature" min="0" max="2" step="0.1" value="{{ $settings['temperature'] }}" 
                           style="width: 100%; height: 6px; background: #e5e7eb; border-radius: 5px; outline: none; appearance: none; cursor: pointer;"
                           oninput="document.getElementById('temp-val').textContent = this.value">
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #9ca3af; margin-top: 8px;">
                        <span>Deterministic</span>
                        <span>Balanced</span>
                        <span>Creative</span>
                    </div>
                </div>

                <div style="margin-bottom: 32px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <label for="max_tokens" style="font-weight: 600; color: #374151;">Max Tokens</label>
                        <span id="tokens-val" style="background: rgba(167, 43, 42, 0.1); color: #a72b2a; padding: 2px 8px; border-radius: 6px; font-weight: 700; font-family: monospace;">{{ $settings['max_tokens'] }}</span>
                    </div>
                    <input type="range" name="max_tokens" id="max_tokens" min="256" max="4096" step="128" value="{{ $settings['max_tokens'] }}" 
                           style="width: 100%; height: 6px; background: #e5e7eb; border-radius: 5px; outline: none; appearance: none; cursor: pointer;"
                           oninput="document.getElementById('tokens-val').textContent = this.value">
                </div>

                <div style="margin-bottom: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <label for="top_p" style="font-weight: 600; color: #374151;">Top P</label>
                        <span id="topp-val" style="background: rgba(167, 43, 42, 0.1); color: #a72b2a; padding: 2px 8px; border-radius: 6px; font-weight: 700; font-family: monospace;">{{ $settings['top_p'] }}</span>
                    </div>
                    <input type="range" name="top_p" id="top_p" min="0" max="1" step="0.05" value="{{ $settings['top_p'] }}" 
                           style="width: 100%; height: 6px; background: #e5e7eb; border-radius: 5px; outline: none; appearance: none; cursor: pointer;"
                           oninput="document.getElementById('topp-val').textContent = this.value">
                </div>
            </div>

            <button type="submit" style="background: #a72b2a; color: #fff; border: none; padding: 12px 32px; border-radius: 12px; font-weight: 600; font-size: 1rem; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                <i class="mdi mdi-content-save-outline"></i>
                Save Configuration
            </button>
        </form>
    </div>
</div>
@endsection
