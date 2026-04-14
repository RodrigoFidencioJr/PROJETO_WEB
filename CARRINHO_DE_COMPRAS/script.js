const produtos = [
  { nome: "Camiseta Dry Fit", preco: 49.90 },
  { nome: "Short Esportivo", preco: 39.90 },
  { nome: "Tênis de Corrida", preco: 199.90 },
  { nome: "Garrafa Térmica", preco: 29.90 },
  { nome: "Luvas de Academia", preco: 59.90 },
  { nome: "Mochila Esportiva", preco: 120.00 },
  { nome: "Meias Esportivas", preco: 19.90 },
  { nome: "Bola de Futebol", preco: 89.90 },
  { nome: "Corda de Pular", preco: 25.00 },
  { nome: "Relógio Esportivo", preco: 250.00 }
];

let carrinho = [];

function listarProdutos(lista) {
  const div = document.getElementById("produtos");
  div.innerHTML = "";

  lista.forEach((produto, index) => {
    const item = document.createElement("div");

    item.innerHTML = `
      ${produto.nome} - R$ ${produto.preco}
      <button onclick="adicionarCarrinho(${index})">
        Adicionar
      </button>
    `;

    div.appendChild(item);
  });
}

function adicionarCarrinho(index) {
  const produto = produtos[index];

  const existe = carrinho.find(p => p.nome === produto.nome);

  if (existe) {
    existe.quantidade++;
  } else {
    carrinho.push({
      nome: produto.nome,
      preco: produto.preco,
      quantidade: 1
    });
  }

  atualizarCarrinho();
}

function atualizarCarrinho() {
  const div = document.getElementById("carrinho");
  div.innerHTML = "";

  carrinho.forEach((item, index) => {
    const produto = document.createElement("div");

    produto.innerHTML = `
      ${item.nome} | Qtd: ${item.quantidade} |
      Total: R$ ${item.preco * item.quantidade}
      <button onclick="removerItem(${index})">
        Remover
      </button>
    `;

    div.appendChild(produto);
  });

  atualizarTotal();
  salvarCarrinho();
}

function removerItem(index) {
  if (carrinho[index].quantidade > 1) {
    carrinho[index].quantidade--;
  } else {
    carrinho.splice(index, 1);
  }

  atualizarCarrinho();
}

function atualizarTotal() {
  let total = 0;

  carrinho.forEach(item => {
    total += item.preco * item.quantidade;
  });

  document.getElementById("total").innerText = total.toFixed(2);
}

function salvarCarrinho() {
  localStorage.setItem("carrinho", JSON.stringify(carrinho));
}

function carregarCarrinho() {
  const dados = localStorage.getItem("carrinho");

  if (dados) {
    carrinho = JSON.parse(dados);
  }

  atualizarCarrinho();
}

document.getElementById("filtro").addEventListener("change", function () {
  const valor = this.value;

  let filtrados;

  switch (valor) {
    case "ate50":
      filtrados = produtos.filter(p => p.preco <= 50);
      break;

    case "acima50":
      filtrados = produtos.filter(p => p.preco > 50);
      break;

    default:
      filtrados = produtos;
  }

  listarProdutos(filtrados);
});

document.addEventListener("DOMContentLoaded", () => {
  listarProdutos(produtos);
  carregarCarrinho();
});