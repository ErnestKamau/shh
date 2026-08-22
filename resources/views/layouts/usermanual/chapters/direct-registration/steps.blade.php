<h2>Step by step</h2>
<ol>
	<li>On Sample Workflow → Samples Receiving, press <strong>Direct Registration</strong>.</li>
	<li><strong>Choose a published testing form</strong> (Food, Water, Swabs, and so on) and open it.</li>
	<li>Walk through the steps: customer → collection → samples → misc → sign.</li>
	<li>Press <strong>Save</strong>. The request moves to Ready for Reception so you can accept samples.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/direct-registration/steps.png',
	'caption' => 'Filling sample details on the form (quantities, type, condition, and so on).',
])
@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/direct-registration/steps-2.png',
	'caption' => 'Last step — conformity, sampled by, remarks, then Save.',
])

<h2>Carousel: jump to Receive Samples</h2>
<p>
	On the sides of the Direct Registration window you will see tall tabs such as
	<strong>Accept Samples</strong> / arrows. That is a <strong>carousel</strong>:
	you can slide straight into the <strong>Receive Samples</strong> window for the same visit
	without closing everything and hunting the board again.
</p>
<ul>
	<li>Use it when you have just saved (or almost finished) registration and want to receive samples next.</li>
	<li>Use the opposite arrow to return to Direct Registration if you need to fix the form.</li>
</ul>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/direct-registration/receive-samples.png',
	'caption' => 'Receive Samples — confirm types, lab, and receive to move the request forward.',
])

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-swap-horizontal"></i></span>
	<p>Carousel = stay in one sitting: register the walk-in form, then receive samples, without losing your place.</p>
</div>
