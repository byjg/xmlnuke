using System;
using System.Collections;
using System.Collections.Specialized;
using System.Xml;

namespace com.xmlnuke.db
{
	public class BTreeUtil
	{
		public const string ComAcento = "ÁáÉéÍíÓóÚúÃãÕõÀàÈèÌìÒòÙùÄäËëÏïÖöÜüÂâÊêÎîÔôÛûÑñÇç";
		public const string SemAcento = "AaEeIiOoUuAaOoAaEeIiOoUuAaEeIiOoUuAaEeIiOoUuNnCc";
		public const string ValidChar = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
		
		private static BTree insertTokens(string documento, string origem, BTree btree)
		{
			string palavra = "";
			char Letra = ' ';
			int j;
			
			// Futuramente esse trecho de código poderá ser retirado para permitir a localização exata do documento.
			j = origem.IndexOf('#');
			if (j >= 0)
			{
				origem = origem.Substring(0, j);
			}
			
			for(int i=0;i<documento.Length; i++)
			{
				Letra = documento[i];
				j = ComAcento.IndexOf(Letra);
				
				// Verificar se a letra possui acento. Se sim, retirar.
				if (j >= 0)
				{
					Letra = SemAcento[j];
				}

				// Determinar se a letra é válida
				if (ValidChar.IndexOf(Letra) >= 0)
				{
					palavra += Letra;
				}
				else
				{
					// Se a palavra formada possuir tamanho menor que dois, ignorar.
					if (palavra.Length > 2)  
					{
						btree = BTree.insert(new BTreeNode(palavra.ToLower(), origem), btree);
					}
					palavra = "";
				}
			}
			// Necessário repetir essa linha para inserir a última palavra!!
			if (palavra.Length > 2)  
			{
				btree = BTree.insert(new BTreeNode(palavra.ToLower(), origem), btree);
			}
			return btree;
		}

		public static ArrayList searchDocuments(string word, BTree btree, bool includeAllDocs)
		{
			NameValueCollection resultAux = new NameValueCollection();
			word = word.ToLower();
			string[] searchFor = word.Split(' ');
			IBTreeNode bnode;
			string aux;
			
			for(int i=0;i<searchFor.Length;i++)
			{
				aux = "";
				for(int j=0;j<searchFor[i].Length;j++)
				{
					int k = ComAcento.IndexOf(searchFor[i][j]);
					// Verificar se a letra possui acento. Se sim, retirar.
					if (k >= 0)
					{
						aux += SemAcento[k];
					}
					else
					{
						aux += searchFor[i][j];
					}
				}
				searchFor[i] = aux;

				bnode = BTree.containsNode(new BTreeNode(searchFor[i]), btree);
				// Se nao existir o no e nao for para incluir tudo, entao houve insucesso na busca
				if (bnode == null)
				{
					if (!includeAllDocs)
					{
						return null;
					}
				}
				// Caso contrário o elemento é adicionado a uma lista temporaria
				else
				{
	        		foreach(string s in bnode.values())
	        		{
	        			aux = resultAux[s];
	        			if (aux == null)
	        			{
	        				resultAux[s] = "1";
	        			}
	        			else
	        			{
	        				resultAux[s] = (Convert.ToInt32(aux) + 1).ToString();
	        			}
	        		}
				}
			}
			
			// Verificar se os elementos podem ser incluidos na resposta.
			// No caso de incluir todos os elementos, sao todos os nos localizados.
			// Ja no caso de obter apenas os elementos procurados, deve-se verificar se a quantidade
			// de elementos inseridos é igual a quantidade de palavras encontradas.
			ArrayList result = new ArrayList();
			foreach(string s in resultAux.Keys)
			{
				if ((resultAux[s] == searchFor.Length.ToString()) || includeAllDocs)
				{
					result.Add(s);
				}
			}
			if (result.Count == 0)
			{
				result = null;
			}
			return result;
			
			
		}
		
		/// <summary>
		/// Search recursively all nodes from a specific DOM document and
		/// insert tokens found in BTREE index.
		/// </summary>
		public static BTree navigateNodes(XmlNode node, string xpath, BTree btree)
		{
			if (node != null)
			{
				if (node.NodeType == XmlNodeType.Text)
				{
					//Console.WriteLine(xpath + "-->" + node.Value); // <-- Fazer aqui o insertTokens
					btree = insertTokens(node.Value, xpath, btree);
				}
				//Console.WriteLine(xpath + node.Name);
				if (node.HasChildNodes)
				{
					btree = navigateNodes(node.ChildNodes[0], xpath + node.Name + "/", btree);
				}
				// Ver se coloco Attributes aqui.... Acho que nao.
				btree = navigateNodes(node.NextSibling, xpath, btree);
			}
			return btree;
			
		}

	}
}
