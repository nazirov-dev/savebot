<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class insert_old_users extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import_old_users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bot = new \App\Services\TelegramService();

        $user_ids = [
            5781212308,
            1996292437,
            6146682996,
            576483825,
            1373321429,
            190561358,
            6099230687,
            984156913,
            1068679174,
            2129378206,
            5212791372,
            1260733448,
            6174882280,
            5571031655,
            1398717388,
            5334222001,
            5572951197,
            6101782550,
            5210268216,
            5253688957,
            2019623850,
            6493391825,
            6605452595,
            1003890858,
            6278866085,
            594479727,
            1289988103,
            5396805442,
            5476974352,
            1166567304,
            704428760,
            5482379049,
            6657519526,
            1240813892,
            190658756,
            6210467178,
            1799901286,
            5521729976,
            834275372,
            5943391959,
            1359437715,
            5856564830,
            648234518,
            6235338990,
            574889275,
            695681058,
            5271517664,
            156859759,
            65456008,
            648401657,
            2019136829,
            6667818695,
            905289936,
            155621399,
            1205140,
            5892736798,
            607855890,
            611557600,
            996579611,
            5066615281,
            181065477,
            747275910,
            964189993,
            104644748,
            1455427810,
            6673669404,
            378356027,
            399039905,
            31243312,
            5687190659,
            1453754091,
            1044883459,
            702500195,
            6134774196,
            5549050180,
            5614026113,
            5559337065,
            5839562954,
            1206934154,
            429245304,
            2087739571,
            470195817,
            6027538162,
            950194601,
            1180558250,
            5098296308,
            97242138,
            1077825723,
            5290178234,
            5986334986,
            102991532,
            5328695558,
            788777393,
            662761054,
            143307205,
            285867858,
            1721421442,
            166521173,
            902698303,
            5423989475,
            513142904,
            1351077107,
            333353643,
            887529914,
            1530175176,
            245197252,
            1585595756,
            867626941,
            701946096,
            665401034,
            990268648,
            5313193814,
            902787914,
            1554529018,
            1888790667,
            1239150460,
            778274611,
            1980665532,
            5218924141,
            1093500311,
            5922537614,
            1241781140,
            197128499,
            5562005376,
            115147060,
            87449456,
            1695647772,
            1968682825,
            5079532016,
            5271898204,
            1998829712,
            68638338,
            874745326,
            5793080219,
            379620453,
            474898363,
            1683704858,
            1912109792,
            1839801082,
            651667583,
            5240625503,
            1111927132,
            5216181026,
            6465845530,
            681029,
            1865998117,
            303078093,
            681932927,
            2142187332,
            579786746,
            575438861,
            5022107702,
            233532817,
            1998341272,
            925412143,
            67963089,
            6531432216,
            640280728,
            591268762,
            1535295786,
            791351564,
            5891864566,
            222424765,
            1690641873,
            732731989,
            1735279382,
            946003082,
            6004478127,
            6300360203,
            474557558,
            1121179021,
            219738407,
            5226365963,
            593661361,
            5409591293,
            5744209554,
            196280149,
            5423637847,
            548902097,
            937083336,
            337228395,
            1092091140,
            1129468266,
            257650444,
            489246672,
            5803500710,
            5175120889,
            5341152355,
            565280558,
            986817826,
            6414755092,
            6521961460,
            5766489218,
            5019363240,
            1044754718,
            6149768821,
            1764479754,
            5736051735,
            1215408130,
            2078957146,
            1072781416,
            5563208252,
            403747532,
            5630174471,
            5438557009,
            1987023327,
            754928503,
            5436634477,
            139760432,
            5688061825,
            382697989,
            806793929,
            6525082182,
            5471177248,
            5493080023,
            1093845324,
            5695005731,
            1166014812,
            109876838,
            2006183324,
            970619819,
            866240722,
            5674895912,
            739458655,
            177539439,
            104521137,
            5617880515,
            2064830802,
            1048259066,
            598811669,
            1856334286,
            1013089936,
            1794573685,
            1271396583,
            441285760,
            5584262579,
            201458175,
            6386705609,
            5563453100,
            697910036,
            5034261606,
            5299194413,
            791566143,
            439275473,
            6263200231,
            271112583,
            844613876,
            61679450,
            1023985125,
            827800231,
            1072365261,
            815015033,
            1595186010,
            445901,
            5050106770,
            953291255,
            5446613013,
            1229087112,
            6534568036,
            5057363768,
            1389133372,
            1141107693,
            6168056599,
            1878915565,
            1162992793,
            1717728214,
            5439873246,
            5944920340,
            5538254530,
            583694712,
            5072109088,
            5483753361,
            5526431292,
            201230222,
            555229850,
            573166482,
            14863798,
            1058217773,
            597365633,
            5269286123,
            5938840151,
            1843710031,
            1958810829,
            1743656322,
            5642719225,
            816320403,
            592315795,
            167035343,
            127796834,
            349712980,
            5349859520,
            626628032,
            1393552337,
            213815009,
            74525059,
            2013165256,
            1117802,
            5648064898,
            853366164,
            794052736,
            45870794,
            68673257,
            5573754011,
            5581567559,
            985462805,
            684684141,
            1910425297,
            1971810352,
            943277366,
            5079845128,
            496753947,
            902895307,
            2061250642,
            582660268,
            135367161,
            1214385338,
            1378667041,
            1692396024,
            1618141172,
            1625946094,
            6192819466,
            5462930,
            718182986,
            1156368701,
            5942231873,
            425785586,
            5042683535,
            5436748151,
            667394362,
            5086363144,
            1389147886,
            954520539,
            5500446858,
            1499827826,
            5722532712,
            1491625297,
            984797264,
            6229238541,
            502542928,
            1571794816,
            1085304456,
            553182549,
            1597946593,
            5840127694,
            5516119781,
            5029360988,
            5800925777,
            305790682,
            6128255431,
            27871470,
            5807664417,
            903407082,
            5479321835,
            5253846101,
            314769267,
            1453291512,
            1234569490,
            195387411,
            1308909898,
            1189291594,
            942934324,
            1663745107,
            1430235211,
            1504748012,
            6584477553,
            475266960,
            667583675,
            869689769,
            1687467115,
            6320197815,
            5381976994,
            1877911972,
            533391391,
            6574846700,
            962268810,
            5469574076,
            1151194316,
            483062343,
            6150324233,
            651173686,
            5786896466,
            875521795,
            5882696316,
            733633934,
            5086532241,
            6027367479,
            930624207,
            5814789110,
            6515167898,
            6499689634,
            180275483,
            482185265,
            5006284753,
            2105936057,
            5396392501,
            533965955,
            5418105077,
            5349186257,
            1885451983,
            5711515313,
            6135495668,
            401478586,
            5836689128,
            1051218754,
            973959400,
            1564838580,
            1133118429,
            1397446518,
            15906794,
            5488371477,
            661758594,
            2105478425,
            998201194,
            313102760,
            443754497,
            594512937,
            1333773378,
            780662825,
            1840257756,
            1961616325,
            1627094412,
            6169626906,
            5220652933,
            2123523873,
            929182988,
            5089369044,
            1239421872,
            5103673511,
            5496054460,
            6560370978,
            6021685444,
            1185006769,
            6375923037,
            35489260,
            1908016498,
            1393330301,
            480262468,
            644803882,
            5747278202,
            312263342,
            5114336945,
            1006374531,
            1643492247,
            1456214811,
            5689198458,
            1015326464,
            1366244293,
            841000750,
            310299188,
            2051858860,
            6209871945,
            6074345126,
            5045029799,
            1776512645,
            6025061447,
            789893712,
            807498870,
            5993446548,
            5935903033,
            6111602460,
            2145049417,
            111937743,
            430167621,
            386386153,
            6091144078,
            5267784030,
            465858989,
            1788413760,
            5505103767,
            1966813889,
            5954276551,
            505156171,
            6563985167,
            457301630,
            2082965505,
            619186448,
            5208773076,
            267628757,
            5706367420,
            1923061672,
            5633565591,
            6387169849,
            555625889,
            6235501687,
            1136997576,
            58598531,
            1551218291,
            1103949951,
            1012987674,
            5853467515,
            5442992692,
            5392306434,
            1172648508,
            757583640,
            1073150608,
            2051694804,
            5608638498,
            1001647711,
            479841939,
            1046732079,
            1123415097,
            923722999,
            1374867759,
            491253008,
            1684400245,
            1892363429,
            1260723814,
            1034235092,
            484689469,
            6410762311,
            1377053406,
            826377033,
            1054012921,
            62592494,
            5434199898,
            5101790456,
            1759544072,
            5384430656,
            1901660611,
            1264740290,
            6460231851,
            757549450,
            5569462441,
            480450484,
            1236316008,
            5707126220,
            762157404,
            1680110540,
            1463214282,
            271031485,
            1066872452,
            514161391,
            372450002,
            859634803,
            799788498,
            5675271287,
            885393845,
            1982186153,
            904270261,
            428319989,
            3622978,
            395500767,
            1793362823,
            1726788998,
            920654107,
            5521450281,
            1340866818,
            5093945443,
            227210913,
            1166504492,
            5157953685,
            1451174700,
            375431376,
            206619973,
            5515655110,
            5392624538,
            1286705813,
            922343580,
            6191643012,
            672272773,
            890606841,
            1985353629,
            6059595682,
            6257927524,
            5956610815,
            6357053220,
            5384625219,
            922403073,
            513896879,
            5430786820,
            5591323686,
            700721551,
            5976688371,
            5372827794,
            5810552389,
            1966840514,
            1977079942,
            6474303557,
            5664290477,
            220291888,
            5742112567,
            682078027,
            5136694273,
            1909210753,
            5452188954,
            1139603942,
            1805880903,
            136808214,
            5487467711,
            340788461,
            5493600760,
            1668170973,
            5874543409,
            1174223956,
            6590090608,
            5651039724,
            293106761,
            218656212,
            1189659407,
            1390644011,
            353454768,
            1641399403,
            1506309397,
            989424344,
            976010421,
            1109313070,
            75746395,
            366164860,
            430647577,
            5237876767,
            107789913,
            6146911629,
            607751587,
            5806762541,
            5897365537,
            1515316084,
            73944905,
            778411810,
            1177953495,
            379531823,
            730029734,
            6206860848,
            5478321100,
            409596036,
            1678164308,
            5773769493,
            672256827,
            6660058214,
            5427959934,
            132801850,
            358675417,
            608681676,
            782351634,
            481490031,
            5957224589,
            5191053827,
            401682855,
            6335981553,
            638562670,
            1877662713,
            1572174176,
            696548275,
            613503613,
            775963501,
            6269236308,
            1612223677,
            1044291350,
            1912199792,
            2002982224,
            1239675667,
            5949192368,
            42535315,
            1380058449,
            570633941,
            1005999772,
            690846182,
            602408385,
            384336181,
            5696316828,
            5290152558,
            5345349545,
            857639916,
            6671521920,
            5537762346,
            775424176,
            497668881,
            2058455243,
            961978456,
            5332961277,
            1089440108,
            507283145,
            5111416770,
            965192292,
            5417123799,
            6481161818,
            343727971,
            1070757219,
            714966594,
            728859798,
            6361323398,
            833815509,
            1520008190,
            5454475526,
            36663469,
            511020896,
            5172468308,
            588312164,
            675368583,
            448125875,
            1457965319,
            5383936180,
            728966640,
            675731167,
            1796693998,
            1294215432,
            1632620315,
            1999464645,
            72928078,
            5910726607,
            700268599,
            54966536,
            625424948,
            5939671775,
            5690079102,
            743501325,
            5378984446,
            870340434,
            5483785509,
            5138384605,
            655235816,
            302480197,
            2140369659,
            2011969305,
            5548322526,
            319616776,
            5118562770,
            1943539983,
            470012689,
            639294712,
            5383845275,
            1180455681,
            5703843947,
            5114089876,
            1584348075,
            159973362,
            283157374,
            2104514790,
            233838601,
            176845243,
            5846641455,
            752737258,
            727218628,
            695671200,
            5025330106,
            6194641728,
            143572573,
            1605959712,
            994454525,
            1886995164,
            1718672344,
            1033836493,
            877816606,
            523156577,
            780478591,
            5448333780,
            904413356,
            5726734086,
            5807755982,
            977337372,
            512731118,
            384909175,
            2065524626,
            1262580830,
            1515088406,
            1342034327,
            5598241530,
            937192004,
            5770032433,
            5754536201,
            1410008723,
            878122253,
            961920522,
            1823406889,
            27080831,
            5451202093,
            605701572,
            5237650169,
            1331765378,
            1603070392,
            664743378,
            5422346044,
            1938849217,
            2080598053,
            1480289052,
            5011257581,
            503434829,
            637929568,
            1791307999,
            87422361,
            1029740378,
            184123856,
            2060565027,
            5453267891,
            1229402726,
            1589079018,
            235549504,
            1252478813,
            5851105688,
            1188158065,
            5754173110,
            5529901390,
            1071550503,
            5121403713,
            402017906,
            287409166,
            5415316785,
            603713220,
            2003190839,
            1024295442,
            5076810966,
            5767000870,
            1744212120,
            414764611,
            5970185486,
            1164291990,
            1874339788,
            1413655750,
            56295607,
            1315623314,
            531734911,
            1026760837,
            63435499,
            327969417,
            554443659,
            297283184,
            766355982,
            878241844,
            572019011,
            1649341087,
            1021375350,
            5268843891,
            5629897321,
            615372337,
            5790559872,
            1131605338,
            5514739401,
            5727703222,
            764712425,
            1309910053,
            1173142598,
            130220080,
            1677552565,
            467693064,
            1921027319,
            66571304,
            179288849,
            1971692532,
            1063918788,
            1057261281,
            5008929438,
            344408885,
            1062701203,
            1942370989,
            1744658601,
            760978330,
            5549565739,
            258900903,
            1200985598,
            5614864712,
            2046694562,
            5069140308,
            499955215,
            234913452,
            369335372,
            5447390455,
            339477527,
            5188677014,
            473398795,
            1103892575,
            695810831,
            1279004418,
            1874337930,
            5351717608,
            968301126,
            5356797386,
            5866556653,
            1709873395,
            5722136008,
            872714173,
            926860770,
            1448885926,
            1871587395,
            138406441,
            1382145461,
            771771698,
            2129584167,
            256032279,
            5579973663,
            169239514,
            2103522611,
            5022284263,
            5996738678,
            650281025,
            984664025,
            795295980,
            5682460999,
            1636073461,
            1217635571,
            2018539089,
            846860639,
            1456396560,
            5084061231,
            5452396704,
            1478637813,
            6136111456,
            98390315,
            478376520,
            355095084,
            275483287,
            835641860,
            1428660162,
            5974377637,
            183864839,
            648468487,
            5041550564,
            613593331,
            5021056847,
            5531028727,
            1918799045,
            5314068667,
            5554830117,
            5193135805,
            1466719782,
            379736626,
            480939249,
            5708197631,
            2082508036,
            1299248464,
            759978447,
            1472446435,
            1905098027,
            5434829469,
            1363749141,
            419150785,
            792653248,
            503995296,
            1167666925,
            5239265379,
            846765416,
            454170402,
            6172024467,
            342168916,
            557877076,
            5522832071,
            891804601,
            1809740295,
            104327896,
            5296623530,
            177506825,
            649895256,
            5040905694,
            5682285835,
            5739445422,
            559067556,
            941310494,
            2114739257,
            538416648,
            5828539908,
            694586571,
            5554963892,
            1345390219,
            744558260,
            1936439850,
            1050239014,
            5179435030,
            129747192,
            1444800773,
            909207127,
            1461986137,
            5592454235,
            1476618241,
            451829399,
            107831656,
            5995271576,
            1445816506,
            1109694764,
            5345899544,
            5452268053,
            5238178272,
            75266545,
            94003679,
            1400333493,
            2024495280,
            5904057280,
            5817472712,
            1887416927,
            1969692484,
            773092575,
            2088098584,
            586708638,
            173786822,
            5046359576,
            1060121873,
            542187344,
            1146326626,
            223557020,
            470724987,
            6295907706,
            1598252858,
            271844814,
            128070707,
            6488844238,
            1870663604,
            119306979,
            5233349691,
            81096701,
            5948179058,
            5239763014,
            783607306,
            569445351,
            346048850,
            773123350,
            1229243320,
            5827878667,
            1485203929,
            834554141,
            5779360341,
            5384163151,
            278133501,
            5743890141,
            903053996,
            1386356264,
            1067331306,
            768055675,
            411628116,
            5950126812,
            5786544976,
            669286240,
            1913337342,
            2115264281,
            1576605717,
            1383297838,
            6256489398,
            5541436586,
            1617730545,
            1077244527,
            944931054,
            302596453,
            1753811096,
            997101383,
            1450658286,
            707547432,
            1924065614,
            761839108,
            322685646,
            207529150,
            38486054,
            975891656,
            398573971,
            5743965568,
            5112767546,
            5944364593,
            1901383619,
            924038307,
            264152894,
            5503535546,
            556667650,
            672846544,
            537335103,
            2084039626,
            1296646574,
            493598222,
            5953414387,
            552484671,
            1111520117,
            314316820,
            692787255,
            5570707505,
            3222876,
            1419513786,
            972235873,
            791564451,
            5758950019,
            1655340564,
            5280255119,
            787577701,
            5405880825,
            763165497,
            1176713001,
            5315278056,
            5676640553,
            5895232542,
            1441924048,
            5250068974,
            860198777,
            542390174,
            5493350066,
            758482122,
            820153313,
            6464223795,
            392775865,
            902008265,
            1668491385,
            853145806,
            5573898810,
            221802088,
            2001984084,
            147213549,
            1340803616,
            669279899,
            365433141,
            410936167,
            138848602,
            230586929,
            582619939,
            5083259600,
            949768165,
            5846569384,
            6005959166,
            764445413,
            5063615052,
            1754901041,
            1783541605,
            2130508980,
            1105671509,
            495668494,
            2024788067,
            1866178329,
            5859025300,
            5506502334,
            5521816611,
            2009269940,
            5315755013,
            5379328147,
            5610650941,
            1091577610,
            295963293,
            1059369700,
            5358468066,
            5007726928,
            444589060,
            5085089819,
            1100087505,
            1698338208,
            137452109,
            5552519360,
            1926904211,
            850012109,
            991000488,
            720556294,
            6171668289,
            1432449486,
            6006057925,
            835959744,
            772389358,
            890853132,
            5789608349,
            158961575,
            548085052,
            5816942241,
            5198722092,
            5749605647,
            824542125,
            1483539116,
            328170532,
            511605165,
            1321198094,
            5563313217,
            5829948764,
            270350294,
            1581632940,
            673720881,
            504587729,
            6263854534,
            433314234,
            1783904196,
            338928216,
            127280163,
            341830360,
            1180821419,
            880584664,
            696217975,
            6286000198,
            6218694728,
            1809837814,
            1813896436,
            5563603514,
            6362840015,
            1266629124,
            309796906,
            565729774,
            5268365949,
            5414442072,
            277298018,
            1061558968,
            543241146,
            1127796247,
            130489310,
            187018262,
            1304858245,
            541343201,
            1571716940,
            389315938,
            1520524372,
            6129346560,
            5280154309,
            5652131467,
            1062963135,
            5824084084,
            1480050379,
            5165354899,
            5701317621,
            5643986741,
            539062566,
            582798638,
            154434073,
            1681907112,
            5574432549,
            5031487608,
            6043126569,
            225094003,
            1842409936,
            436592527,
            1561463200,
            5752351029,
            335028914,
            365014204,
            558383428,
            1902728391,
            1627966240,
            1739415001,
            1057786333,
            1793924450,
            2133717287,
            5242294208,
            276365979,
            5426161625,
            959842299,
            5369020289,
            1487688118,
            1866971,
            1970520999,
            1495113714,
            5401811776,
            928665851,
            1983080308,
            1925460289,
            1163366132,
            607963547,
            1673472022,
            5074613177,
            249339062,
            879014899,
            1531815436,
            1321504492,
            67286256,
            411480286,
            1969274070,
            872370363,
            895156921,
            921898059,
            5365881050,
            565357455,
            5839556624,
            1496275793,
            1530945716,
            5550022859,
            5034807133,
            347469888,
            5014188980,
            5454313917,
            5354218878,
            1262051857,
            1089951157,
            5664080205,
            1050859734,
            805491944,
            5254137617,
            162920031,
            1846120383,
            5062134144,
            1664857272,
            5013772106,
            1234235574,
            2074208302,
            888105674,
            832007985,
            92751640,
            5686462628,
            5518492430,
            5765270662,
            956101461,
            6216825645,
            185021381,
            1378285307,
            6176820706,
            1424569019,
            292175429,
            1760537706,
            455526371,
            391476314,
            156997557,
            5372917676,
            5196764747,
            1154780459,
            95304109,
            5246404834,
            1137568167,
            631671447,
            861159940,
            1647820980,
            6488561113,
            1138222809,
            1084922549,
            624138751,
            508874117,
            853189767,
            634064377,
            130126522,
            5579568808,
            5217881906,
            1976647399,
            5591446377,
            5563475829,
            55039096,
            5046600516,
            109074460,
            5308108456,
            153800746,
            1786453315,
            10982857,
            444334489,
            469801430,
            292236613,
            1487459207,
            130481184,
            106953038,
            1836288,
            1782251602,
            5288255441,
            5046595487,
            1248341555,
            871452748,
            5841815303,
            514819671,
            1373242899,
            1872976697,
            990165567,
            5836658120,
            5000454250,
            1825147674,
            874770174,
            5701527005,
            1364130496,
            5874436846,
            878185338,
            519796744,
            1866040136,
            5490067210,
            1637852158,
            605995304,
            322114170,
            299545045,
            1816014853,
            311136802,
            660337807,
            1083660580,
            138153080,
            1016763372,
            5390087885,
            206620722,
            1785396774,
            349229033,
            901648544,
            481693287,
            5973497889,
            1383332086,
            250074980,
            737242614,
            5515390174,
            1054308543,
            5381227568,
            1947557839,
            1658776855,
            782262116,
            1923630935,
            367952186,
            5014034727,
            5362573895,
            192893145,
            5259204490,
            757964667,
            610253712,
            674399982,
            1216668806,
            6193260235,
            916912674,
            798925548,
            811331249,
            682703675,
            2017880716,
            58011760,
            67636500,
            5685873724,
            5914567618,
            2010375234,
            5660394129,
            515644888,
            954362801,
            358522539,
            831281875,
            245767289,
            1627661538,
            5967119245,
            5152507665,
            723289185,
            316023676,
            914453746,
            5298099682,
            624547388,
            500142970,
            1870147836,
            1224099471,
            5189483045,
            246184174,
            893009498,
            5051067559,
            5338273552,
            664531075,
            5884903147,
            5247652837,
            571699203,
            77140295,
            757446679,
            499781804,
            1904993310,
            623843380,
            6116710343,
            43437943,
            5384024432,
            5176008548,
            1953314428,
            882805626,
            458809440,
            287760454,
            501071084,
            2071757274,
            5286103365,
            252237453,
            517017842,
            687659447,
            5286276615,
            2010301208,
            485326351,
            160608341,
            5984494643,
            303871201,
            5010103462,
            6041815371,
            271028641,
            509092781,
            1773579120,
            6377050606,
            2076867197,
            1335508748,
            1370211819,
            252593494,
            777475100,
            5348397116,
            729383168,
            1740202289,
            1874174211,
            267014673,
            153768392,
            569378156
        ];
        $first_lang = \App\Models\Lang::find(1);
        $added = 0;
        $not_added = 0;
        $active_users = 0;
        $not_active_users = 0;
        $tried = [];
        foreach ($user_ids as $user_id) {
            $get_chat = $bot->getChat([
                'chat_id' => $user_id
            ]);

            if ($get_chat['ok']) {
                $is_active = $bot->sendChatAction(['chat_id' => $user_id, 'action' => 'typing'])['ok'];
                \App\Models\BotUser::create([
                    'user_id' => $user_id,
                    'lang_code' => $first_lang->short_code,
                    'name' => $get_chat['result']['first_name'],
                    'username' => (isset($get_chat['result']['username']) && !empty($get_chat['result']['username'])) ? $get_chat['result']['username'] : null,
                    'status' => $is_active
                ]);
                $added++;
                if ($is_active) {
                    $active_users++;
                } else {
                    $not_active_users++;
                }
            } else {
                $not_added++;
            }

            usleep(200000);
        }

        // Define the format for printing summary details
        $format = "| %-25s | %-10s |\n";
        $border = "+--------------------------+------------+\n";

        // Print the table header
        echo $border;
        printf($format, 'Ma\'lumot', 'Soni');
        echo $border;

        // Print summary details
        printf($format, 'Qo\'shildi', $added);
        printf($format, 'Qo\'shilmadi', $not_added);
        printf($format, 'Faol foydalanuvchilar', $active_users);
        printf($format, 'No faol foydalanuvchilar', $not_active_users);
        echo $border;
    }
}
