<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Rules\ValidDocument;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Lista os clientes (GET /api/customers)
     */
    public function index()
    {
        // Busca os clientes ordenados pelo nome, 10 por página
        return Customer::orderBy('name')->paginate(10);
    }

    /**
     * Cadastra um cliente (POST /api/customers)
     */
    public function store(Request $request)
    {
        $customer = Customer::create($this->validateCustomer($request));

        // 201 = criado com sucesso
        return response()->json($customer, 201);
    }

    /**
     * Mostra um cliente (GET /api/customers/{id})
     */
    public function show(Customer $customer)
    {
        // O Laravel já buscou o cliente pelo id da URL
        return $customer;
    }

    /**
     * Edita um cliente (PUT /api/customers/{id})
     */
    public function update(Request $request, Customer $customer)
    {
        // Passa o cliente atual para a validação ignorar o próprio CPF/CNPJ
        $customer->update($this->validateCustomer($request, $customer));

        return $customer;
    }

    /**
     * Exclui um cliente (DELETE /api/customers/{id})
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        // 204 = deu certo, sem conteúdo para devolver
        return response()->noContent();
    }

    /**
     * Validação usada no cadastro e na edição.
     */
    private function validateCustomer(Request $request, ?Customer $customer = null): array
    {
        // Tira a máscara do documento antes de validar e salvar
        $request->merge([
            'document' => normalizeDocument((string) $request->document),
        ]);

        return $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'document' => [
                'required',
                new ValidDocument,
                Rule::unique('customers')->ignore($customer?->id),
            ],
            'email'    => ['nullable', 'email', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'active'   => ['boolean'],
        ], [
            'name.required'     => 'Informe o nome.',
            'document.required' => 'Informe o CPF/CNPJ.',
            'document.unique'   => 'Este documento já está cadastrado.',
            'email.email'       => 'E-mail inválido.',
        ]);
    }
}