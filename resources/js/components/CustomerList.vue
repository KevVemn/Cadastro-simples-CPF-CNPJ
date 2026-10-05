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
    <!-- Fundo em degradê ocupando a tela toda -->
    <div class="page">

        <!-- ===== CARD DO FORMULÁRIO ===== -->
        <form @submit.prevent="saveCustomer" class="card form-card">
            <h1>{{ form.id ? 'Editar cliente' : 'Cadastrar' }}</h1>

            <!-- Nome -->
            <label class="field">
                <span class="field-label">Nome</span>
                <div class="input-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="8" r="4" />
                        <path d="M4 20c0-4 4-6 8-6s8 2 8 6" />
                    </svg>
                    <input v-model="form.name" placeholder="Nome completo">
                </div>
                <small v-if="errors.name">{{ errors.name[0] }}</small>
            </label>

            <!-- CPF / CNPJ -->
            <label class="field">
                <span class="field-label">CPF / CNPJ</span>
                <div class="input-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="5" width="18" height="14" rx="2" />
                        <circle cx="9" cy="12" r="2" />
                        <path d="M14 10h4M14 14h4" />
                    </svg>
                    <input v-model="form.document" placeholder="000.000.000-00">
                </div>
                <small v-if="errors.document">{{ errors.document[0] }}</small>
            </label>

            <!-- E-mail -->
            <label class="field">
                <span class="field-label">E-mail</span>
                <div class="input-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="5" width="18" height="14" rx="2" />
                        <path d="m3 7 9 6 9-6" />
                    </svg>
                    <input v-model="form.email" type="email" placeholder="email@exemplo.com">
                </div>
                <small v-if="errors.email">{{ errors.email[0] }}</small>
            </label>

            <!-- Telefone -->
            <label class="field">
                <span class="field-label">Telefone</span>
                <div class="input-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 4h4l2 5-3 2a11 11 0 0 0 5 5l2-3 5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2" />
                    </svg>
                    <input v-model="form.phone" placeholder="(00) 00000-0000">
                </div>
            </label>

            <label class="checkbox">
                <input v-model="form.active" type="checkbox"> Cliente ativo
            </label>

            <button type="submit" class="btn-primary">
                {{ form.id ? 'Atualizar' : 'Cadastrar' }}
            </button>
            <button v-if="form.id" type="button" class="btn-secondary" @click="resetForm">
                Cancelar
            </button>
        </form>

        <!-- ===== CARD DA TABELA ===== -->
        <div class="card table-card">
            <h2>Clientes cadastrados</h2>

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
                        <td>
                            <span :class="['badge', customer.active ? 'badge-active' : 'badge-inactive']">
                                {{ customer.active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="row-actions">
                            <button class="btn-link" @click="editCustomer(customer)">Editar</button>
                            <button class="btn-link danger" @click="deleteCustomer(customer)">Excluir</button>
                        </td>
                    </tr>
                    <tr v-if="!customers.length">
                        <td colspan="5" class="empty">Nenhum cliente encontrado.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
/* ---------- Fundo da página ---------- */
.page {
    min-height: 100vh;
    padding: 48px 16px;
    background: linear-gradient(135deg, #ff5f6d 0%, #ff7e5f 50%, #ffc371 100%);
    font-family: 'Poppins', sans-serif;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 24px;
}

/* ---------- Cards brancos ---------- */
.card {
    width: 100%;
    background: #fff;
    border-radius: 12px;
    padding: 32px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}
.form-card { max-width: 380px; display: grid; gap: 14px; }
.table-card { max-width: 820px; }

h1 { font-size: 24px; font-weight: 600; color: #2d2d2d; margin-bottom: 4px; }
h2 { font-size: 18px; font-weight: 600; color: #2d2d2d; margin-bottom: 12px; }

/* ---------- Campos ---------- */
.field { display: grid; gap: 4px; }
.field-label { font-size: 13px; font-weight: 500; color: #333; }

/* A "pílula" que envolve o ícone + o input */
.input-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border: 1.5px solid #555;
    border-radius: 999px;
    transition: border-color 0.2s, box-shadow 0.2s;
}
/* Quando o campo de dentro está com foco, a borda fica laranja */
.input-wrapper:focus-within {
    border-color: #ff7e5f;
    box-shadow: 0 0 0 3px rgba(255, 126, 95, 0.2);
}
.input-wrapper svg { width: 18px; height: 18px; color: #ff7e5f; flex-shrink: 0; }
.input-wrapper input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    font: inherit;
    font-size: 14px;
}

small { color: #e11d48; font-size: 12px; padding-left: 14px; }

.checkbox { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #333; }
.checkbox input { accent-color: #ff7e5f; width: 16px; height: 16px; }

/* ---------- Botões ---------- */
.btn-primary {
    margin-top: 6px;
    padding: 10px;
    border-radius: 999px;
    background: linear-gradient(90deg, #ff5f6d, #ffc371);
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s;
}
.btn-primary:hover { opacity: 0.9; }

.btn-secondary {
    padding: 10px;
    border-radius: 999px;
    background: #eee;
    color: #555;
    font-weight: 500;
    cursor: pointer;
}

.btn-link { color: #ff7e5f; font-weight: 500; font-size: 13px; cursor: pointer; }
.btn-link:hover { text-decoration: underline; }
.btn-link.danger { color: #e11d48; }

/* ---------- Tabela ---------- */
table { width: 100%; border-collapse: collapse; font-size: 14px; }
th {
    text-align: left;
    padding: 10px 8px;
    font-size: 12px;
    font-weight: 500;
    color: #888;
    text-transform: uppercase;
    border-bottom: 1px solid #eee;
}
td { padding: 12px 8px; border-bottom: 1px solid #f2f2f2; color: #333; }
.row-actions { display: flex; gap: 12px; }
.empty { text-align: center; color: #999; }

/* Etiquetas de status */
.badge { padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 500; }
.badge-active { background: #dcfce7; color: #166534; }
.badge-inactive { background: #f3f4f6; color: #6b7280; }
</style>