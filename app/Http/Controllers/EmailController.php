<?php

namespace App\Http\Controllers;

use App\Models\EmailNotification;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function index()
    {
        $emails = EmailNotification::all();
        return view('pages.email.index', compact('emails'));
    }

    public function create()
    {
        return view('pages.email.create');
    }

    public function store(Request $request)
    {
        $request->validate(['email' => 'required|email|unique:email_notifications,email']);

        EmailNotification::create($request->all());

        return redirect()->route('email.index')->with('success', 'Email berhasil ditambahkan!');
    }

    public function show($id)
    {
        // Tidak diperlukan untuk kasus ini
    }

    public function edit(EmailNotification $email)
    {
        return view('pages.email.edit', compact('email'));
    }

    public function update(Request $request, EmailNotification $email)
    {
        $request->validate(['email' => 'required|email|unique:email_notifications,email,' . $email->id]);

        $email->update($request->all());

        return redirect()->route('email.index')->with('success', 'Email berhasil diperbarui!');
    }

    public function destroy(EmailNotification $email)
    {
        $email->delete();

        return redirect()->route('email.index')->with('success', 'Email berhasil dihapus!');
    }
}
