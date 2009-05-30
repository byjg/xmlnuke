//=============================================================================
// System  : JavaScript Compressor
// File    : JSCompressor.cs
// Author  : Eric Woodruff  (Eric@EWoodruff.us)
// Updated : 06/26/2006
// Note    : Copyright 2003-2006, Eric Woodruff, All rights reserved
// Compiler: Visual C#
//
// This class is used to compress JavaScript code by removing all comments,
// extraneous whitespace, and line feeds where possible (optional).
//
// This code may be used in compiled form in any way you desire.  This
// file may be redistributed unmodified by any means PROVIDING it is not
// sold for profit without the author's written consent, and providing
// that this notice and the author's name and all copyright notices
// remain intact.
//
// This code is provided "as is" with no warranty either express or
// implied.  The author accepts no liability for any damage or loss of
// business that this product may cause.
//
// Version     Date     Who  Comments
// ============================================================================
// 1.0.0.0  07/21/2002  EFW  Created the code
// 2.0.0.0  03/04/2006  EFW  Rebuilt and tested with VS 2005 and .NET 2.0 and
//                           added support for compressing variable names.
// 2.0.0.1  06/26/2006  EFW  Modified to support conditional compilation blocks
//=============================================================================

using System;
using System.Collections.Specialized;
using System.Text.RegularExpressions;

namespace com.xmlnuke.util
{
	/// <summary>
	/// This class is used to compress JavaScript code.
	/// </summary>
	/// <remarks>Compression in this case consists of removing comments
	/// and unnecessary whitespace.  It can also remove almost all line
	/// feed characters between lines provided that semi-colons have been
	/// used to indicate all statement endpoints.</remarks>
	public class JSCompressor
	{
		//=====================================================================
		// Private data members

		// Line feed removal and variable name compression flags
		private bool removeLineFeeds, compressVarNames, varCompTest;

		// Variable name compression tracking
		private char[] newVarName;
		private int varNamePos;

		// Uncompressed sections removed by #pragma
		private StringCollection scNoComps;

		// Literals removed during initial pass
		private StringCollection scLiterals;

		// Regular expression and match evaluator used to re-insert literals
		// and uncompressed sections.
		private Regex reInsLit, reExtNoComp, reDelNoComp, reFuncParams,
				reFindVars, reStripVarPrefix, reStripParens, reStripAssign;
		private MatchEvaluator meExtNoComp, meInsLit;
		private int literalCount, noCompCount;

		//=====================================================================
		// Properties

		/// <summary>
		/// Get or set the line feed removal option.
		/// </summary>
		public bool LineFeedRemoval
		{
			get { return removeLineFeeds; }
			set { removeLineFeeds = value; }
		}

		/// <summary>
		/// Get or set the variable name compression option.
		/// </summary>
		public bool CompressVariableNames
		{
			get { return compressVarNames; }
			set { compressVarNames = value; }
		}

		/// <summary>
		/// This is used to test variable name compression only
		/// </summary>
		/// <remarks>If set to true, only variable names will be compressed.
		/// This makes it easier to debug possible issues with the variable
		/// name compression code.</remarks>
		public bool TestVariableNameCompression
		{
			get { return varCompTest; }
			set { varCompTest = value; }
		}

		//=====================================================================
		// Methods, etc

		/// <summary>
		/// Default constructor.  Line feed removal defaults to true, variable
		/// name compression defaults to false.
		/// </summary>
		/// <overloads>There are three overloads for the constructor.</overloads>
		public JSCompressor()
			: this(true)
		{
		}

		/// <summary>
		/// This version takes the line feed removal option.  Variable name
		/// compression defaults to false.
		/// </summary>
		/// <param name="removeLFs">Pass true to remove line feeds wherever
		/// possible, false to leave them in the script.</param>
		public JSCompressor(bool removeLFs)
		{
			removeLineFeeds = removeLFs;
			scLiterals = new StringCollection();
			scNoComps = new StringCollection();

			// TODO: TESTING - Enable variable compression by default
			compressVarNames = true;
		}

		/// <summary>
		/// This version takes the line feed removal option and variable name
		/// compression option.
		/// </summary>
		/// <param name="removeLFs">Pass true to remove line feeds wherever
		/// possible, false to leave them in the script.</param>
		/// <param name="compressVars">Pass true to compress variable names
		/// or false to leave variable names intact.</param>
		public JSCompressor(bool removeLFs, bool compressVars)
			: this(removeLFs)
		{
			compressVarNames = compressVars;
		}

		/// <summary>
		/// Compress the specified JavaScript code.
		/// </summary>
		/// <param name="strScript">The script to compress</param>
		/// <returns>The compressed script</returns>
		public string Compress(string strScript)
		{
			string strCompressed;
			char[] achScriptChars;

			// Don't bother if there is nothing to compress
			if (strScript == null || strScript.Length == 0)
				return strScript;

			// Set up for compression
			scLiterals.Clear();
			scNoComps.Clear();

			// Create the regular expressions and match evaluators on
			// first use.
			if (reInsLit == null)
			{
				reExtNoComp = new Regex(@"//\s*#pragma\s*NoCompStart.*?" +
					@"//\s*#pragma\s*NoCompEnd.*?\n",
					RegexOptions.Multiline | RegexOptions.Singleline |
					RegexOptions.IgnoreCase);
				reDelNoComp = new Regex(@"//\s*#pragma\s*NoComp(Start|End).*\n",
					RegexOptions.Multiline | RegexOptions.IgnoreCase);
				reInsLit = new Regex("\xFE|\xFF");
				meInsLit = new MatchEvaluator(OnMarkerFound);
				meExtNoComp = new MatchEvaluator(OnNoCompFound);

				reFuncParams = new Regex(@"function.*?\((.*?)\)(.*?|\n)?\{",
					RegexOptions.IgnoreCase | RegexOptions.Singleline);
				reFindVars = new Regex(@"(var\s+.*?)(;|$)",
					RegexOptions.IgnoreCase | RegexOptions.Multiline);
				reStripVarPrefix = new Regex(@"^var\s+",
					RegexOptions.IgnoreCase);
				reStripParens = new Regex(@"\(.*?,.*?\)|\[.*?,.*?\]",
					RegexOptions.IgnoreCase);
				reStripAssign = new Regex(@"(=.*?)(,|;|$)",
					RegexOptions.IgnoreCase);
			}

			// Extract sections that the user doesn't want compressed
			// and replace them with a marker.
			strCompressed = reExtNoComp.Replace(strScript, meExtNoComp);

			// Split the string into an array for parsing
			achScriptChars = strCompressed.ToCharArray();

			// Remove comments and extract literals
			CompressArray(achScriptChars);

			// Gather up what's left and remove the nulls
			strCompressed = new String(achScriptChars);
			strCompressed = strCompressed.Replace("\0", String.Empty);

			// Skip code compression?
			if (!varCompTest)
			{
				// Remove all leading and trailing whitespace and condense runs
				// of two or more whitespace characters to just one.
				strCompressed = Regex.Replace(strCompressed, @"^[\s]+|[ \f\r\t\v]+$",
					String.Empty, RegexOptions.Multiline);
				strCompressed = Regex.Replace(strCompressed, @"([\s]){2,}", "$1");

				// Line feed removal requested?
				if (removeLineFeeds)
				{
					// Remove line feeds when they appear near numbers with signs
					// or operators.  A space is used between + and - occurrences
					// in case they are increment/decrement operators followed by
					// an add/subtract operation.  In other cases, line feeds are
					// only removed following a + or - if it is not part of an
					// increment or decrement operation.
					strCompressed = Regex.Replace(strCompressed, @"([+-])\n\1",
						"$1 $1");
					strCompressed = Regex.Replace(strCompressed, @"([^+-][+-])\n",
						"$1");
					strCompressed = Regex.Replace(strCompressed,
						@"([\xFE{}([,<>/*%&|^!~?:=.;])\n", "$1");
					strCompressed = Regex.Replace(strCompressed,
						@"\n([{}()[\],<>/*%&|^!~?:=.;+-])", "$1");
				}

				// Strip all unnecessary whitespace around operators
				strCompressed = Regex.Replace(strCompressed,
					@"[ \f\r\t\v]?([\n\xFE\xFF/{}()[\];,<>*%&|^!~?:=])[ \f\r\t\v]?",
					"$1");
				strCompressed = Regex.Replace(strCompressed, @"([^+]) ?(\+)",
					"$1$2");
				strCompressed = Regex.Replace(strCompressed, @"(\+) ?([^+])",
					"$1$2");
				strCompressed = Regex.Replace(strCompressed, @"([^-]) ?(\-)",
					"$1$2");
				strCompressed = Regex.Replace(strCompressed, @"(\-) ?([^-])",
					"$1$2");

				// Try for some additional line feed removal savings by
				// stripping them out from around one-line if, while,
				// and for statements and cases where any of those
				// statements immediately follow another.
				if (removeLineFeeds)
				{
					strCompressed = Regex.Replace(strCompressed,
						@"(\W(if|while|for)\([^{]*?\))\n", "$1");
					strCompressed = Regex.Replace(strCompressed,
						@"(\W(if|while|for)\([^{]*?\))((if|while|for)\([^{]*?\))\n",
						"$1$3");
					strCompressed = Regex.Replace(strCompressed,
						@"([;}]else)\n", "$1 ");
				}
			}

			// Compress variable names too if requested
			if (compressVarNames || varCompTest)
				strCompressed = CompressVariables(strCompressed);

			// Put back the literals and uncompressed sections removed
			// during the parsing step.
			noCompCount = literalCount = 0;
			strCompressed = reInsLit.Replace(strCompressed, meInsLit);

			return strCompressed;
		}

		// Replace literals with a marker so that they don't interfere with
		// subsequent parsing steps.
		private void ExtractLiteral(char[] achScriptChars, int nStartPos,
			int nEndPos)
		{
			int nLen = nEndPos - nStartPos + 1;

			scLiterals.Add(new String(achScriptChars, nStartPos, nLen));

			achScriptChars[nStartPos] = '\xFF';

			Array.Clear(achScriptChars, nStartPos + 1, nLen - 1);
		}

		// Determine if we have the start of a regular expression statement
		private static bool IsRegExpStart(char[] achScriptChars, int nCurPos)
		{
			char ch;

			while (nCurPos-- > 0)
			{
				ch = achScriptChars[nCurPos];

				// If not whitespace, see if it's an open parenthesis,
				// semi-colon, or equal sign.  If so, we have the start of
				// a regular expression.
				if (Char.IsWhiteSpace(ch) == false)
					return (ch == '(' || ch == ';' || ch == '=') ? true : false;
			}

			return true;
		}

		// Parse the input array and compress it by removing comments and
		// pulling out literals so they don't interfere with the final
		// compression steps.
		private void CompressArray(char[] achScriptChars)
		{
			bool bInComment = false;
			int nIdx, nLen = achScriptChars.Length, nStartPos = -1;
			char chCur, chNext, chEnd = '\0';

			for (nIdx = 0; nIdx < nLen; nIdx++)
			{
				chCur = achScriptChars[nIdx];

				// Are we inside a comment, quoted string, or reg exp?
				if (nStartPos > -1)
				{
					if (bInComment == true)
					{
						// Check for end of block comment.  Remove the entire
						// block comment if the end is found.
						if (chEnd == '*')
						{
							if (nIdx - nStartPos > 2 &&
							  achScriptChars[nIdx - 1] == '*' && chCur == '/')
							{
								Array.Clear(achScriptChars, nStartPos,
									nIdx - nStartPos + 1);
								nStartPos = -1;
								bInComment = false;
							}
						}
						else
							if (chCur == '\r' || chCur == '\n')
							{
								// End of single-line comment, remove section
								Array.Clear(achScriptChars, nStartPos,
									nIdx - nStartPos + 1);
								nStartPos = -1;
								bInComment = false;
							}
					}
					else    // Found ending quote or reg exp marker?
						if (chCur == chEnd)
						{
							ExtractLiteral(achScriptChars, nStartPos, nIdx);
							nStartPos = -1;
						}
						else    // Skip escape sequences
							if (chCur == '\\')
								nIdx++;
				}
				else
					if (nIdx < nLen - 1)
					{
						// Start of comment or regular expression?
						if (chCur == '/')
						{
							chNext = achScriptChars[nIdx + 1];

							if (chNext == '*' || chNext == '/')
							{
								nStartPos = nIdx++;
								chEnd = chNext;

								// Ignore the comment if it's the start of
								// conditional compilation (/*@).
								if (nIdx < nLen - 1 && chNext == '*' &&
								  achScriptChars[nIdx + 1] == '@')
									nStartPos = -1;
								else
									bInComment = true;
							}
							else
								if (JSCompressor.IsRegExpStart(achScriptChars, nIdx))
								{
									// Regular expression
									nStartPos = nIdx;
									chEnd = chCur;
								}
						}
						else
							if (chCur == '\'' || chCur == '\"')
							{
								// Quoted string
								chEnd = chCur;
								nStartPos = nIdx;
							}
							else    // Convert CRs to LFs when found
								if (chCur == '\r' && !varCompTest)
									achScriptChars[nIdx] = '\n';
					}
			}
		}

		// Replace a literal or uncompressed section marker with the
		// next entry from the appropriate collection.
		private string OnMarkerFound(Match match)
		{
			if (match.Value == "\xFE")
				return scNoComps[noCompCount++];

			return scLiterals[literalCount++];
		}

		// Extract the sections that the user doesn't want compressed
		// and save them for reinsertion at the end without the #pragmas.
		// They are replaced with a marker character.
		private string OnNoCompFound(Match match)
		{
			scNoComps.Add(reDelNoComp.Replace(match.Value, String.Empty));
			return "\xFE";
		}

		// This is used to compress variable names
		private string CompressVariables(string script)
		{
			StringCollection scVariables = new StringCollection();
			string[] varNames;
			string name = null, matchName;
			bool incVarName;

			// Find function parameters
			MatchCollection matches = reFuncParams.Matches(script);

			foreach (Match m in matches)
			{
				varNames = m.Groups[1].Value.Split(',');

				// Add each unique name to the list
				foreach (string s in varNames)
				{
					name = s.Trim();

					if (name.Length != 0 && !scVariables.Contains(name))
						scVariables.Add(name);
				}
			}

			// Find variable declarations
			matches = reFindVars.Matches(script);

			foreach (Match m in matches)
			{
				// Remove the "var " declaration prefix
				name = reStripVarPrefix.Replace(m.Groups[1].Value, String.Empty);

				// Strip brackets and parentheses containing commas such
				// as array declarations and method calls with parameters.
				name = reStripParens.Replace(name, String.Empty);

				// Remove assignment operations
				name = reStripAssign.Replace(name, "$2");

				varNames = name.Split(',');

				// Add each unique name to the list
				foreach (string s in varNames)
				{
					name = s.Trim();

					if (name.Length != 0 && !scVariables.Contains(name))
						scVariables.Add(name);
				}
			}

			// Replace each variable in the list with a shorter name.
			// Start with "a" through "z" then use "_a" through "_z",
			// "_aa" to "_az", "_ba" to "_bz", etc.
			newVarName = new char[10];
			newVarName[0] = '\x60';
			varNamePos = 0;
			incVarName = true;

			foreach (string replaceName in scVariables)
			{
				// Increment the variable name and make sure it isn't
				// in use already.
				if (incVarName)
				{
					do
					{
						IncrementVariableName();

						name = new String(newVarName, 0, varNamePos + 1);
						matchName = @"\W" + name + @"\W";

					} while (Regex.IsMatch(script, matchName));

					incVarName = false;
				}

				// Don't bother if the existing name is shorter.  This check
				// could be removed to obfuscate the variable name even if it
				// would be longer.
				if (name.Length < replaceName.Length)
				{
					incVarName = true;
					script = Regex.Replace(script,
						@"(\W)" + replaceName + @"(?=\W)", "$1" + name);
				}
			}

			return script;
		}

		// This is used to increment the compressed variable name
		private void IncrementVariableName()
		{
			if (newVarName[varNamePos] != 'z')
				newVarName[varNamePos]++;
			else
			{
				// After "a" through "z" prefix with an underscore to make
				// sure we don't match a keyword or function name.
				if (varNamePos == 0)
				{
					newVarName[0] = '_';
					varNamePos++;
				}
				else
				{
					// _a to _z, _aa to _az, _ba to _bz, etc
					if (newVarName[varNamePos - 1] == '_' ||
					  newVarName[varNamePos - 1] == 'z')
					{
						if (newVarName[varNamePos - 1] == '_')
							newVarName[varNamePos] = 'a';
						else
							newVarName[varNamePos - 1] = 'a';

						varNamePos++;
					}
					else
						newVarName[varNamePos - 1]++;
				}

				newVarName[varNamePos] = 'a';
			}
		}
	}
}