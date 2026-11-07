@extends('layouts.app')

@section('title', 'ProfitFix Module Settings')

@section('content')
<div class="card-body">
    <div class="card col-md-12">
        <div class="card-header bg-dark text-white">
            <h1 class="text-center mb-0">ProfitFix Module Settings</h1>
        </div>
    </div>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header text-white d-flex align-items-center">
                    
                    <h4 class="mb-"><i class="fas fa-cogs me-2"></i> ProfitFix Module Settings</h4>
                </div>
                <div class="card-body bg-light">
                    <div class="alert alert-info mb-4 shadow-sm">
                        <strong><i class="fas fa-info-circle me-1"></i> Module Description:</strong><br>
                        <span>
                            ProfitFix is an advanced profit/loss reporting module for UltimatePOS. It provides accurate, filterable, and tabbed profit/loss reports using the <code>corrected_profit_lines</code> view, resolves SQL ambiguities, and offers a user-friendly interface with DataTables, AJAX filtering, and a missed profit status bar. The module integrates seamlessly with the admin sidebar and includes comprehensive documentation and installation instructions.
                        </span>
                    </div>
                    
                    <!-- Single line buttons section -->
                    <div class="bg-white p-4 mb-4 rounded border text-center">
    <div class="d-flex justify-content-center align-items-center" style="gap: 1rem;">
        <form method="POST" action="{{ route('profitfix.applyfix') }}" style="display: inline-block;">
            @csrf
            <button type="submit" class="btn btn-success">
                <i class="fas fa-check me-1"></i>Apply Fix
            </button>
        </form>
        
        <form method="POST" action="{{ route('profitfix.removefix') }}" style="display: inline-block;">
            @csrf
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash me-1"></i>Remove Fix
            </button>
        </form>
    </div>
</div>
                    
                    <div class="mb-4">
                        <h5 class="fw-bold"><i class="fas fa-star me-1"></i> Key Features:</h5>
                        <ul class="list-group mb-3">
                            <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Accurate profit/loss calculation using <code>corrected_profit_lines</code> view</li>
                            <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Accurate sale transactions calculation using <code>corrected_profit_lines</code> view</li>
                            <li class="list-group-item"><i class="fas fa-table text-primary me-2"></i>Tabbed, filterable reports with DataTables and AJAX</li>
                            <li class="list-group-item"><i class="fas fa-chart-line text-info me-2"></i>Missed profit status bar for quick insights</li>
                            <li class="list-group-item"><i class="fas fa-th-large text-secondary me-2"></i>Seamless admin sidebar integration</li>
                            <li class="list-group-item">Module Auther : Telegram @omgklrahul<br>Module Version : 1.0.0<br></li>
</ul>
                    </div>
                    <div class="mb-4">
                        <h5 class="fw-bold"><i class="fas fa-question-circle me-1"></i>Attribution</h5>
                        <p>
Nulled By <a href="https://t.me/omgklrahul" target="_blank">Telegram @omgklrahul</a> Contact on <a href="https://t.me/omgklrahul" target="_blank">For more scripts </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection