import datetime


class Utils:
    @staticmethod
    def sToTimeFormat(s, format='%H:%M:%S.%f'):
        s = float(s)
        delta = datetime.timedelta(milliseconds=s * 1000)
        f = (datetime.datetime.utcfromtimestamp(0) + delta).strftime(format)[:-3]
        print(f)
        return f
