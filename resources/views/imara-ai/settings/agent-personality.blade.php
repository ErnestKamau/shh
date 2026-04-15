@extends('layouts.ImaraAi.manager')

@section('kb-content')
<div class="manager-content">
    <div class="settings-header" style="margin-bottom: 32px;">
        <h1 style="font-size: 1.875rem; font-weight: 700; color: #111827; margin-bottom: 8px;">Agent Personality</h1>
        <p style="color: #6b7280; font-size: 1.05rem;">Define how Imarachat AI identifies itself and interacts with your team.</p>
    </div>

    @if(session('success'))
        <div style="background: #ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 16px; border-radius: 12px; margin-bottom: 24px;">
            <i class="mdi mdi-check-circle" style="margin-right: 8px;"></i>
            {{ session('success') }}
        </div>
    @endif

    <div style="width: 100%;">
        <form action="{{ route('ai.settings.agent-personality.update') }}" method="POST">
            @csrf
            
            <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 24px;">
                <div style="margin-bottom: 24px;">
                    <label for="agent_name" style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px;">Agent Name</label>
                    <input type="text" name="agent_name" id="agent_name" value="{{ $settings['agent_name'] }}" 
                           style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem; color: #111827;"
                           placeholder="e.g. Imarachat AI">
                </div>

                <div style="margin-bottom: 24px;">
                    <label for="tone_of_voice" style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px;">Tone of Voice</label>
                    <select name="tone_of_voice" id="tone_of_voice" 
                            style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem; color: #111827; background: #fff;">
                        <option value="Professional" {{ $settings['tone_of_voice'] == 'Professional' ? 'selected' : '' }}>Professional & Clinical</option>
                        <option value="Friendly" {{ $settings['tone_of_voice'] == 'Friendly' ? 'selected' : '' }}>Friendly & Helpful</option>
                        <option value="Concise" {{ $settings['tone_of_voice'] == 'Concise' ? 'selected' : '' }}>Concise & Direct</option>
                        <option value="Empathetic" {{ $settings['tone_of_voice'] == 'Empathetic' ? 'selected' : '' }}>Empathetic & Detailed</option>
                    </select>
                </div>

                <div style="margin-bottom: 0;">
                    <label for="system_prompt" style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px;">Core System Instructions (System Prompt)</label>
                    <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 12px;">This is the foundation of the AI's identity. It defines its purpose, knowledge limits, and behavioral constraints.</p>
                    <textarea name="system_prompt" id="system_prompt" rows="20" 
                              style="width: 100%; padding: 16px; border: 1px solid #d1d5db; border-radius: 12px; font-family: 'Inter', ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.95rem; line-height: 1.6; color: #111827;"
                              placeholder="e.g. You are Imarachat AI, a professional medical laboratory assistant...">{{ $settings['system_prompt'] }}</textarea>
                </div>
            </div>

            <div style="display: flex; gap: 16px; align-items: center;">
                <button type="submit" style="background: #a72b2a; color: #fff; border: none; padding: 12px 32px; border-radius: 12px; font-weight: 600; font-size: 1rem; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <i class="mdi mdi-content-save-outline"></i>
                    Save Personality
                </button>
                <button type="button" onclick="resetPrompt()" style="background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; padding: 12px 24px; border-radius: 12px; font-weight: 600; font-size: 1rem; cursor: pointer;">
                    Reset to Default
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

<script>
function resetPrompt() {
    if (confirm('Are you sure you want to reset the core instructions to the system default?')) {
        document.getElementById('system_prompt').value = `{!! addslashes($defaults['system_prompt'] ?? '') !!}`;
        document.getElementById('agent_name').value = "{{ $defaults['agent_name'] ?? 'Imarachat AI' }}";
        document.getElementById('tone_of_voice').value = "{{ $defaults['tone_of_voice'] ?? 'Professional' }}";
    }
}
</script>
