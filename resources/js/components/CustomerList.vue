<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const customers = ref([]);
const errors = ref({});

// Agora o molde tem "id": null = cadastro novo, com número = edição
const emptyForm = { id: null, name: '', document: '', email: '', phone: '', active: true };
const form = ref({ ...emptyForm });

// READ
async function fetchCustomers() {
    const response = await axios.get('/api/customers');
    customers.value = response.data.data;
}

// CREATE ou UPDATE: decide pelo id
async function saveCustomer() {
    errors.value = {};

    try {
        if (form.value.id) {
            // Tem id: é edição (PUT /api/customers/2)
            await axios.put(`/api/customers/${form.value.id}`, form.value);
        } else {
            // Sem id: é cadastro (POST /api/customers)
            await axios.post('/api/customers', form.value);
        }

        resetForm();
        fetchCustomers();
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        } else {
            alert('Erro ao salvar.');
        }
    }
}

// Coloca o cliente clicado dentro do formulário (uma CÓPIA dele)
function editCustomer(customer) {
    form.value = { ...customer };
    errors.value = {};
}

// Volta o formulário para vazio
function resetForm() {
    form.value = { ...emptyForm };
    errors.value = {};
}

// DELETE: pergunta antes de excluir
async function deleteCustomer(customer) {
    if (!confirm(`Excluir ${customer.name}?`)) return;

    await axios.delete(`/api/customers/${customer.id}`);
    fetchCustomers();
}

onMounted(() => fetchCustomers());
</script>

<template>
    <div class="container">
        <h1>Clientes</h1>

        <form @submit.prevent="saveCustomer" class="card">
            <!-- O título muda conforme o modo: novo ou edição -->
            <h2>{{ form.id ? 'Editar cliente' : 'Novo cliente' }}</h2>

            <label>Nome
                <input v-model="form.name">
                <small v-if="errors.name">{{ errors.name[0] }}</small>
            </label>

            <label>CPF / CNPJ
                <input v-model="form.document">
                <small v-if="errors.document">{{ errors.document[0] }}</small>
            </label>

            <label>E-mail
                <input v-model="form.email" type="email">
                <small v-if="errors.email">{{ errors.email[0] }}</small>
            </label>

            <label>Telefone
                <input v-model="form.phone">
            </label>

            <label class="checkbox">
                <input v-model="form.active" type="checkbox"> Ativo
            </label>

            <div class="actions">
                <button type="submit">{{ form.id ? 'Atualizar' : 'Cadastrar' }}</button>

                <!-- type="button": sem isso, o Cancelar SALVARIA o formulário -->
                <button v-if="form.id" type="button" class="secondary" @click="resetForm">Cancelar</button>
            </div>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Documento</th>
                    <th>E-mail</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="customer in customers" :key="customer.id">
                    <td>{{ customer.name }}</td>
                    <td>{{ customer.document }}</td>
                    <td>{{ customer.email ?? '-' }}</td>
                    <td>{{ customer.active ? 'Ativo' : 'Inativo' }}</td>
                    <td class="row-actions">
                        <!-- Cada botão recebe o cliente DAQUELA linha -->
                        <button class="small" @click="editCustomer(customer)">Editar</button>
                        <button class="small danger" @click="deleteCustomer(customer)">Excluir</button>
                    </td>
                </tr>
                <tr v-if="!customers.length">
                    <td colspan="5">Nenhum cliente encontrado.</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
.container { max-width: 900px; margin: 32px auto; padding: 0 16px; font-family: sans-serif; }
h1 { font-size: 28px; font-weight: bold; margin-bottom: 16px; }
h2 { font-size: 20px; font-weight: bold; }
.card { display: grid; gap: 8px; padding: 16px; border: 1px solid #ccc; border-radius: 8px; margin-bottom: 24px; }
label { display: grid; gap: 4px; }
.checkbox { display: flex; align-items: center; gap: 6px; }
input { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
.actions { display: flex; gap: 8px; }
button { padding: 8px 16px; background: #2563eb; color: white; border-radius: 4px; cursor: pointer; }
button.secondary { background: #6b7280; }
button.small { padding: 4px 10px; font-size: 13px; }
button.danger { background: #dc2626; }
.row-actions { display: flex; gap: 6px; }
small { color: #dc2626; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 8px; border-bottom: 1px solid #ddd; text-align: left; }
</style>